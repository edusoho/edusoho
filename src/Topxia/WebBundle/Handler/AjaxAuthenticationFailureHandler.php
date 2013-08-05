<?php
 
namespace Topxia\WebBundle\Handler;
 
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
 
class AjaxAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{

    protected $translator;

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => false,
                'message' => $this->translator->trans($exception->getMessage())
            );
            return new JsonResponse($content, 400);
        }
 
        return parent::onAuthenticationFailure($request, $exception);
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}