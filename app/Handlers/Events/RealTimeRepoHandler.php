<?php

/*
 * This file is part of StyleCI.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 * (c) Joseph Cohen <joseph.cohen@dinkbit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StyleCI\StyleCI\Handlers\Events;

use McCool\LaravelAutoPresenter\PresenterDecorator;
use StyleCI\StyleCI\Events\RepoWasDisabledEvent;
use StyleCI\StyleCI\Events\RepoWasEnabledEvent;
use StyleCI\StyleCI\Repositories\UserRepository;
use Vinkla\Pusher\PusherManager;

/**
 * This is the real time repo handler class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class RealTimeRepoHandler
{
    /**
     * The user repository instance.
     *
     * @var \StyleCI\StyleCI\Repositories\UserRepository
     */
    protected $userRepository;

    /**
     * The pusher instance.
     *
     * @var \Vinkla\Pusher\PusherManager
     */
    protected $pusher;

    /**
     * The presenter instance.
     *
     * @var \McCool\LaravelAutoPresenter\PresenterDecorator
     */
    protected $presenter;

    /**
     * Create a new repo notifications handler instance.
     *
     * @param \StyleCI\StyleCI\Repositories\UserRepository    $userRepository
     * @param \Vinkla\Pusher\PusherManager                    $pusher
     * @param \McCool\LaravelAutoPresenter\PresenterDecorator $presenter
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, PusherManager $pusher, PresenterDecorator $presenter)
    {
        $this->userRepository = $userRepository;
        $this->pusher = $pusher;
        $this->presenter = $presenter;
    }

    /**
     * Handle the repo has been enabled or disabled event.
     *
     * @param \StyleCI\StyleCI\Events\RepoWasDisabledEvent|\StyleCI\StyleCI\Events\RepoWasEnabledEvent $event
     *
     * @return void
     */
    public function handle($event)
    {
        $repo = $this->presenter->decorate($event->getRepo());

        if ($event instanceof RepoWasDisabledEvent) {
            $data = ['event' => $repo->toArray(), 'disabled' => true];
        } elseif ($event instanceof RepoWasEnabledEvent) {
            $data = ['event' => $repo->toArray(), 'enabled' => true];
        }

        $this->pusher->trigger('ch-'.$repo->id, 'RepoUpdatedEvent', $data);

        foreach ($this->userRepository->collaborators($event->getRepo()) as $user) {
            $this->pusher->trigger('repos-'.$user->id, 'RepoUpdatedEvent', ['event' => $data]);
        }
    }
}
