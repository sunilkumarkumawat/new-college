<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Session;


class isLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next 
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        
        
       
        $loginWithOtp = Setting::first();
          $ignoreRoutes = [
            'is-Login',
            'sendWhatsapp',
            'validateOtpWhatsapp'
        ];
        if($loginWithOtp->loginWithOtp == 'Yes' && session()->get('role_id') != 1 )
        {
            
            
             if(session()->get('otp_request') != 'accepted' && session()->get('id') != '' )
             {
                if (!in_array($request->path(), $ignoreRoutes)) {
                 
              return redirect()->route('access.denied');
                }
                 
             }
              // return redirect('logout');
            
        }
       
     
        if(session()->get('id') != ''){
            if (empty(session()->get('session_id')) && session()->get('role_id') != 3) {
                $dynamicSession = \App\Helpers\helper::resolveCurrentFinancialSession(session()->get('branch_id'));
                session()->put('session_id', $dynamicSession->id);
            }
        }else{
            return redirect()->intended('login');
        }
        
        // if (!session()->has('validated_on') || session('validated_on') !== date('Y-m-d'))
        // if (($r = app(\App\Services\RuntimeSync::class)->RuntimeSyncCheck($request)) instanceof \Illuminate\Http\Response) return $r;
      
        return $next($request);
    }
}

