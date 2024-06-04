<?php declare(strict_types=1);

namespace App\Application\Actions\Common\User;

use App\Domain\Service\User\Exception\EmailAlreadyExistsException;
use App\Domain\Service\User\Exception\EmailBannedException;
use App\Domain\Service\User\Exception\MissingUniqueValueException;
use App\Domain\Service\User\Exception\PhoneAlreadyExistsException;
use App\Domain\Service\User\Exception\UsernameAlreadyExistsException;
use App\Domain\Service\User\Exception\WrongEmailValueException;
use App\Domain\Service\User\Exception\WrongPhoneValueException;
use App\Domain\Service\User\Exception\WrongUsernameValueException;

class UserRegisterAction extends UserAction
{
    protected function action(): \Slim\Psr7\Response
    {
        if ($this->isPost()) {
            if ($this->isRecaptchaChecked()) {
                $provider = $this->getParam('provider', $_SESSION['auth_provider'] ?? 'BasicAuthProvider');

                try {
                    $this->auth->register($provider, [
                        'firstname' => $this->getParam('firstname', ''),
                        'lastname' => $this->getParam('lastname', ''),
                        'username' => $this->getParam('username', ''),
                        'email' => $this->getParam('email', ''),
                        'phone' => $this->getParam('phone', ''),
                        'address' => $this->getParam('address', ''),
                        'additional' => $this->getParam('additional', ''),
                        'is_allow_mail' => $this->getParam('is_allow_mail', true),
                        'password' => $this->getParam('password'),
                        'password_again' => $this->getParam('password_again'),
                        'external_id' => $this->getParam('external_id', ''),
                    ]);

                    return $this->respondWithRedirect('/user/login');
                } catch (MissingUniqueValueException $e) {
                    $this->addError('email', $e->getMessage());
                    $this->addError('username', $e->getMessage());
                    $this->addError('phone', $e->getMessage());
                } catch (UsernameAlreadyExistsException|WrongUsernameValueException $e) {
                    $this->addError('username', $e->getMessage());
                } catch (EmailAlreadyExistsException|EmailBannedException|WrongEmailValueException $e) {
                    $this->addError('email', $e->getMessage());
                } catch (PhoneAlreadyExistsException|WrongPhoneValueException $e) {
                    $this->addError('phone', $e->getMessage());
                }
            }

            $this->addError('grecaptcha', 'EXCEPTION_WRONG_GRECAPTCHA');
        }

        return $this->respond($this->parameter('user_register_template', 'user.register.twig'));
    }
}
