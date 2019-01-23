<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTRefresh
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws TokenBlacklistedException
     */
    public function handle($request, Closure $next)
    {
        $token = $this->authenticate($request);
        $response = $next($request);

        if ($token) {
            $response->header('Authorization', 'Bearer ' . $token);
        }

        return $response;
    }

    /**
     * Check the request for the presence of a token.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return void
     */
    public function checkForToken(Request $request)
    {
        if (!Auth::parser()->setRequest($request)->hasToken()) {
            throw new UnauthorizedHttpException('jwt-auth', 'Token not provided');
        }
    }

    /**
     * Attempt to authenticate a user via the token in the request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return bool
     * @throws TokenBlacklistedException
     */
    public function authenticate(Request $request)
    {
        if (Auth::guard('api')->user()) {
            return false;
        }

        $this->checkForToken($request);
        try {
            if (!Auth::guard('api')->payload()) {
                throw new UnauthorizedHttpException('jwt-auth', 'User not found');
            }
        } catch (TokenExpiredException $e) {
            // If the token is expired, then it will be refreshed and added to the headers
            try {
                return Auth::guard('api')->refresh();
            } catch (TokenExpiredException $e) {
                throw new UnauthorizedHttpException('jwt-auth', 'Refresh token has expired.');
            }
        } catch (TokenBlacklistedException $e) {
            throw new TokenBlacklistedException($e->getMessage(), 401);
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }
    }
}
