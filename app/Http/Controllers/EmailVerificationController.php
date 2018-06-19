<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Models\User;
use Cache;
use App\Notifications\EmailVerificationNotification;
use Mail;
use App\Exceptions\InvalidRequestException;


class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        //从URL里取 'email'和'token'两个参数
        $email = $request->input('email');
        $token = $request->input('token');
        //如果有一个为空说明不是合法的验证链接,直接抛出异常
        if(!$email || !$token){
            throw new InvalidRequestException('验证链接不正确');
        }
        //从缓存中读取数据，我们把从url中获取的’token'与缓存中的值做对比
        //如果缓存不存在或者返回的值与url中的token不一致就抛出异常.
        if($token != Cache::get('email_verification_'.$email)){
            throw new InvalidRequestException('验证链接不正确或者已过期');
        }

        //根据邮箱从数据库获取对应的用户
        //通常来说能通过token校验的情况不可能出现用户不存在
        //但为了代码的健壮性我们还是做这个判断
        if(!$user = User::where('email',$email)->first()){
            throw new InvalidRequestException('用户不存在');
        }
        //将指定的key从缓存中删除，由于已经完成验证，缓存没必要存在
        Cache::forget('email_verification_'.$email);
        //将email_verified字段改为true
        $user->update(['email_verified' => true ]);
        //最后告知用户邮箱验证成功
        return view('pages.success',['msg'=>'邮箱验证成功']);

    }

    public function send(Request $request)
    {
        $user = $request->user();
        if($user->email_verified){
            throw new InvalidRequestException('你已经验证过邮箱了');
        }
        $user->notify(new EmailVerificationNotification());
        return view('pages.success',['msg'=>'邮件发送成功']);

    }

}
