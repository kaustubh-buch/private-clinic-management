<?php

namespace App\Http\Middleware\Clinic;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailVerification
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $users_id = session()->get('verify_user_id');
        if (! empty($users_id)) {
            $user = $this->userRepository->find($users_id);

            if ($user && $user->email_verified_at === null) {
                return $next($request);
            }
        }

        return redirect()->route('dashboard');
    }
}
