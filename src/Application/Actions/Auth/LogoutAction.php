<?php declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Common\User\UserAction;
use App\Domain\Traits\SecurityTrait;

class LogoutAction extends UserAction
{
    use SecurityTrait;

    protected function action(): \Slim\Psr7\Response
    {
        /** @var \App\Domain\Entities\User $user */
        $user = $this->request->getAttribute('user', false);

        if ($user) {
            $refresh_token = $this->getCookie('refresh_token', null);

            if ($refresh_token) {
                /** @var \App\Domain\Entities\User\Token $token */
                $token = $user->getTokens()->firstWhere('unique', $refresh_token);

                if ($token) {
                    $this->userTokenService->delete($token);
                }
            }

            setcookie('access_token', '', time(), '/');
            setcookie('refresh_token', '', time(), '/auth');

            $this->container->get(\App\Application\PubSub::class)->publish('common:user:logout', $user);
        }

        return $this->respondWithRedirect('/');
    }
}
