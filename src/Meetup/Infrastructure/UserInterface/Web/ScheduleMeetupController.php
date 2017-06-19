<?php
declare(strict_types = 1);

namespace Meetup\Infrastructure\UserInterface\Web;

use Meetup\Application\ScheduleMeetup;
use Meetup\Application\ScheduleMeetupHandler;
use Meetup\Domain\Model\MeetupRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

final class ScheduleMeetupController
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ScheduleMeetupHandler
     */
    private $meetupScheduler;

    /**
     * @var MeetupRepository
     */
    private $repository;

    public function __construct(
        TemplateRendererInterface $renderer,
        RouterInterface $router,
        ScheduleMeetupHandler $meetupScheduler,
        MeetupRepository $repository
    ) {
        $this->renderer = $renderer;
        $this->router = $router;
        $this->meetupScheduler = $meetupScheduler;
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $formErrors = [];
        $submittedData = [];

        if ($request->getMethod() === 'POST') {
            $submittedData = $request->getParsedBody();

            $command = new ScheduleMeetup();

            $command->id = (string)$this->repository->nextIdentity();
            $command->name = $submittedData['name'];
            $command->description = $submittedData['description'];
            $command->scheduledFor = $submittedData['scheduledFor'];

            $formErrors = $command->validate();

            if (empty($formErrors)) {
                $meetup = $this->meetupScheduler->handle($command);

                return new RedirectResponse(
                    $this->router->generateUri(
                        'meetup_details',
                        [
                            'id' => $meetup->meetupId()
                        ]
                    )
                );
            }
        }

        $response->getBody()->write(
            $this->renderer->render(
                'schedule-meetup.html.twig',
                [
                    'submittedData' => $submittedData,
                    'formErrors' => $formErrors
                ]
            )
        );

        return $response;
    }
}
