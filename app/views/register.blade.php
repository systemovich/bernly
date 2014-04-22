@extends( 'layouts.master' )

@section( 'content' )

	<div class="container">
	
		<h1 class="page-header">Register</h1>
	
		<form action="/user/register" method="post" role="form">
		
			<div class="form-group {{ Session::get( 'email_class' ) }}">
				<label for="email" class="control-label">Email address</label>
				<input 
                    type="text" 
                    id="email" class="form-control" 
                    name="email" value="{{{ Session::get( 'email' ) }}}"
                >
                <p class="help-block">{{ Session::get('email_error' ) }}</p>
			</div>
			
			<div class="form-group {{ Session::get( 'password_class' ) }}">
				<label for="password" class="control-label">Password</label>
				<input 
                    type="password" 
                    id="password" class="form-control" 
                    name="password" value="{{{ Session::get( 'password' ) }}}"
                >
                <p class="help-block">{{ Session::get( 'password_error' ) }}</p>
			</div>
			
			<div class="form-group {{ Session::get( 'confirm_password_class' ) }}">
				<label for="confirm_password" class="control-label">Confirm Password</label>
				<input 
                    type="password" 
                    id="confirm_password" class="form-control" 
                    name="confirm_password" value="{{{ Session::get( 'confirm_password' ) }}}"
                >
                <p class="help-block">{{ Session::get( 'confirm_password_error' ) }}</p>
			</div>
			
			<button class="btn btn-primary">Submit</button>
			
		</form>
		
		<hr>
		
	</div>
	
@stop