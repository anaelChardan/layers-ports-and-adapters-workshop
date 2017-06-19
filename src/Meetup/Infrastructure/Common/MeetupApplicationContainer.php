<?php
/**
 * Created by PhpStorm.
 * User: anael
 * Date: 19/06/17
 * Time: 12:16
 */

namespace Meetup\Infrastructure\Common;

use Interop\Container\ContainerInterface;
use Meetup\Application\Notify;
use Meetup\Application\ScheduleMeetupHandler;
use Meetup\Domain\Model\MeetupRepository;
use Meetup\Infrastructure\Notification\LogNotifier;
use Meetup\Infrastructure\Notification\NotifiersRegistry;
use Meetup\Infrastructure\Notification\RabbitMqNotifier;
use Meetup\Infrastructure\UserInterface\Cli\ScheduleMeetupConsoleHandler;
use Meetup\Infrastructure\UserInterface\Web\ListMeetupsController;
use Meetup\Infrastructure\UserInterface\Web\MeetupDetailsController;
use Meetup\Infrastructure\Persistence\FileBased\FileBasedMeetupRepository;
use Meetup\Infrastructure\UserInterface\Web\Resources\Views\TwigTemplates;
use Meetup\Infrastructure\UserInterface\Web\ScheduleMeetupController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Webmozart\Console\ConsoleApplication;
use Xtreamwayz\Pimple\Container;
use Zend\Expressive\Application;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRendererFactory;

class MeetupApplicationContainer extends Container
{
    public function __construct(string $rootDir)
    {
        parent::__construct(['root_dir' => $rootDir]);

        Debug::enable();
        ErrorHandler::register();

        $this['config'] = [
            'debug' => true,
            'templates' => [
                'extension' => 'html.twig',
                'paths' => [
                    TwigTemplates::getPath()
                ]
            ],
            'twig' => [
                'extensions' => [
                ]
            ],
            'routes' => [
                [
                    'name' => 'list_meetups',
                    'path' => '/',
                    'middleware' => ListMeetupsController::class,
                    'allowed_methods' => ['GET']
                ],
                [
                    'name' => 'meetup_details',
                    'path' => '/meetup/{id:.+}',
                    'middleware' => MeetupDetailsController::class,
                    'allowed_methods' => ['GET']
                ],
                [
                    'name' => 'schedule_meetup',
                    'path' => '/schedule-meetup',
                    'middleware' => ScheduleMeetupController::class,
                    'allowed_methods' => ['GET', 'POST']
                ]
            ]
        ];

        /*
         * Zend Expressive Application
         */
        $this['Zend\Expressive\FinalHandler'] = function () {
            return function (RequestInterface $request, ResponseInterface $response, $err = null) {
                if ($err instanceof \Throwable) {
                    throw $err;
                }
            };
        };
        $this[RouterInterface::class] = function () {
            return new FastRouteRouter();
        };
        $this[Application::class] = new ApplicationFactory();

        /*
         * Templating
         */
        $this[TemplateRendererInterface::class] = new TwigRendererFactory();
        $this[ServerUrlHelper::class] = function () {
            return new ServerUrlHelper();
        };
        $this[UrlHelper::class] = function (ContainerInterface $container) {
            return new UrlHelper($container[RouterInterface::class]);
        };

        /*
         * Persistence
         */
        $this[MeetupRepository::class] = function (ContainerInterface $container) {
            return new FileBasedMeetupRepository($container['root_dir'] . '/var/meetups.txt');
        };

        /*
         * Controllers
         */
        $this[ScheduleMeetupController::class] = function (ContainerInterface $container) {
            return new ScheduleMeetupController(
                $container->get(TemplateRendererInterface::class),
                $container->get(RouterInterface::class),
                $container->get(ScheduleMeetupHandler::class),
                $container->get(MeetupRepository::class)
            );
        };
        $this[ListMeetupsController::class] = function (ContainerInterface $container) {
            return new ListMeetupsController(
                $container->get(MeetupRepository::class),
                $container->get(TemplateRendererInterface::class)
            );
        };
        $this[MeetupDetailsController::class] = function (ContainerInterface $container) {
            return new MeetupDetailsController(
                $container->get(MeetupRepository::class),
                $container->get(TemplateRendererInterface::class)
            );
        };

        /*
         * CLI
         */
        $this[ScheduleMeetupConsoleHandler::class] = function (ContainerInterface $container) {
            return new ScheduleMeetupConsoleHandler(
                $container->get(ScheduleMeetupHandler::class),
                $container->get(MeetupRepository::class)
            );
        };

        /*
         * Schedule Meetup
         */
        $this[ScheduleMeetupHandler::class] = function (ContainerInterface $container) {
            return new ScheduleMeetupHandler($container->get(MeetupRepository::class), $container->get(Notify::class));
        };

        $this[Notify::class] = function () {
            return new NotifiersRegistry([
                new LogNotifier(),
                new RabbitMqNotifier()
            ]);
        };
    }

    public function getConsoleApplication(): ConsoleApplication
    {
        return $this[ConsoleApplication::class];
    }

    public function getWebApplication(): Application
    {
        return $this[Application::class];
    }
}