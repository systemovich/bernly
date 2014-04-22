<?php

class UserController extends BaseController {

	public function getIndex()
	{
		return Redirect::to( '/' );
	}

	public function getRegister()
	{
		return View::make( 'register' );
	}
	
	public function postRegister()
	{
        $email = Input::get( 'email' );
        $password = Input::get( 'password' );
        $confirm_password = Input::get( 'confirm_password' );
        
        if ( ! $this->isEmailValid( $email ) ) {
            return Redirect::to( '/user/register' )->with( array(
                'email_class' => 'has-error',
                'email_error' => 'The email you entered is invalid. It should be similar to john@example.com',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password
            ));
        }
        
        if ( ! $this->isPasswordValid( $password ) ) {
            return Redirect::to( '/user/register' )->with( array(
                'password_class' => 'has-error',
                'password_error' => 'Password should be at least 10 characters long.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password
            ));
        }
        
        if ( ! $this->isPasswordConfirmed( $password, $confirm_password ) ) {
            return Redirect::to( '/user/register' )->with( array(
                'confirm_password_class' => 'has-error',
                'confirm_password_error' => 'The passwords do not match.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password
            ));
        }
        
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make( $password );
        $user->setRememberToken( 'remember' );
        $user->save();
        
        return Redirect::to('/');
	}
	
	private function isEmailValid( $email )
	{
        return preg_match( 
            "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", 
            $email 
        );
	}
	
    private function isPasswordValid( $password )
    {
        return strlen( $password ) >= 10;
    }
    
    private function isPasswordConfirmed( $password, $confirm_password )
    {
        return $password === $confirm_password;
    }
    
	public function getLogin()
	{
		return View::make( 'login' );
	}
	
	public function postLogin()
	{
        $email = Input::get( 'email' );
        $password = Input::get( 'password' );
        $remember_me = Input::get( 'remember_me' );
        
        if (Auth::attempt( array( 'email' => $email, 'password' => $password ), $remember_me ) ) {
            return Redirect::intended('/');
        }
        
        return Redirect::to( '/user/login' )->with( array(
            'error' => true,
            'email' => $email,
            'password' => $password
        ));
	}
	
	public function getLogout()
	{
        Auth::logout();
        
        return Redirect::to( '/' );
	}
}