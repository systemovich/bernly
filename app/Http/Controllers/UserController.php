<?php namespace Bernly\Http\Controllers;

use DateTime;
use DateTimeZone;

use Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use ReCaptcha\ReCaptcha;

use Bernly\Helpers\UrlHelper;
use Bernly\Helpers\UserHelper;
use Bernly\Models\Url;
use Bernly\Models\User;


class UserController extends Controller
{
    /**
     * @summary Responds to HTTP GET /user. Displays home page.
     *
     * @return Response
     */
    public function getIndex()
    {
        return redirect('/');
    }

    /**
     * @summary Responds to HTTP GET /user/add. Displays user registration form.
     *
     * @return Response
     */
    public function getAdd()
    {
        return view('user.add')->with(
            'timezones',
            DateTimeZone::listIdentifiers(DateTimeZone::ALL)
       );
    }

    /**
     * @summary Responds to HTTP POST /user/add. Creates new user in database, then displays home page.
     *
     * @return Response
     */
    public function postAdd(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $confirm_password = $request->input('confirm_password');
        $timezone = $request->input('timezone');
        $recaptcha_response = $request->input('g-recaptcha-response');

        $recaptcha = new ReCaptcha(config('recaptcha.secret_key'));
        $recaptcha_result = $recaptcha->verify($recaptcha_response);

        if (! UserHelper::isEmailValid($email)) {
            return redirect('/user/add')->with([
                'email_class' => 'has-error',
                'email_error' => 'The email you entered is invalid. It should be similar to john@example.com',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password,
                'timezone' => $timezone
            ]);
        }

        if (! UserHelper::isPasswordValid($password)) {
            return redirect('/user/add')->with([
              'password_class' => 'has-error',
              'password_error' => 'Password should be at least 10 characters long.',
              'email' => $email,
              'password' => $password,
              'confirm_password' => $confirm_password,
              'timezone' => $timezone
            ]);
        }

        if (! UserHelper::isPasswordConfirmed($password, $confirm_password)) {
            return redirect('/user/add')->with([
                'confirm_password_class' => 'has-error',
                'confirm_password_error' => 'The passwords do not match.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password,
                'timezone' => $timezone
            ]);
        }

        if ($timezone === 'Please select ...') {
            return redirect('/user/add')->with([
                'timezone_class' => 'has-error',
                'timezone_error' => 'A timezone is required.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password
            ]);
        }

        if (! $recaptcha_result->isSuccess()) {
            return redirect('user/add')->with([
                'recaptcha_class' => 'has-error',
                'recaptcha_error' => 'Please complete the reCAPTCHA.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password,
                'timezone' => $timezone
            ]);
        }

        $user = new User;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->timezone = $timezone;
        $user->setRememberToken('remember');

        try {
            $user->save();
        } catch (QueryException $exception) {
            return redirect('/user/add')->with([
                'email_class' => 'has-error',
                'email_error' => 'The email you entered has already been used.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password,
                'timezone' => $timezone
            ]);
        }

        auth()->login($user);
        return redirect('/verify');
    }

    /**
     * @summary Responds to HTTP GET /user/view. Displays the logged-in user's profile.
     *
     * @return Response
     */
    public function getView()
    {
          return view('user.view');
    }

    /**
     * @summary Responds to HTTP GET /user/edit-email. Displays email edit form.
     *
     * @return Response
     */
    public function getEditEmail()
    {
        return view('user.edit-email');
    }

    /**
     * @summary Responds to HTTP POST /user/edit-email. Updates logged-in user's email address, then
     * displays user's profile with a success message.
     *
     * @return Response
     */
    public function postEditEmail(Request $request)
    {
        $email = $request->input('email');

        if (! UserHelper::isEmailValid($email)) {
            return redirect('/user/edit-email')->with([
                'email_class' => 'has-error',
                'email_error' => 'The email you entered is invalid. It should be similar to john@example.com',
                'email' => $email
            ]);
        }

        $user = User::find(auth()->user()->id);
        $user->email = $email;
        $user->verified = false;
        $user->save();

        return redirect('/verify')->with('is_edited_email', true);
    }

    /**
     * @summary Responds to HTTP GET /user/edit-password. Displays password edit form.
     *
     * @return Response
     */
    public function getEditPassword()
    {
        return view('user.edit-password');
    }

    /**
     * @summary Responds to HTTP POST /user/edit-password. Updates logged-in user's password, then displays
     * user's profile with a success message.
     *
     * @return Response
     */
    public function postEditPassword(Request $request)
    {
        $password = $request->input('password');
        $confirm_password = $request->input('confirm_password');

        if (! UserHelper::isPasswordValid($password)) {
            return redirect('/user/edit-password')->with(array(
                'password_class' => 'has-error',
                'password_error' => 'Password should be at least 10 characters long.',
                'email' => $email,
                'password' => $password,
                'confirm_password' => $confirm_password
           ));
        }

        if (! UserHelper::isPasswordConfirmed($password, $confirm_password)) {
            return redirect('/user/edit-password')->with(array(
                'confirm_password_class' => 'has-error',
                'confirm_password_error' => 'The passwords do not match.',
                'password' => $password,
                'confirm_password' => $confirm_password
           ));
        }

        $user = User::find(auth()->user()->id);
        $user->password = Hash::make($password);
        $user->save();

        return redirect('/user/view')->with('is_edited_password', true);
    }

    /**
     * @summary Responds to HTTP GET /user/edit-timezone. Displays timezone edit form.
     *
     * @return Response
     */
    public function getEditTimezone()
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return view('user.edit-timezone')->with('timezones', $timezones);
    }

    /**
     * @summary Responds to HTTP POST /user/edit-timezone. Updates logged-in user's timezone, then
     * displays user's profile with a success message.
     *
     * @return Response
     */
    public function postEditTimezone(Request $request)
    {
        $timezone = $request->input('timezone');

        if ($timezone === 'Please select ...') {
            return redirect('/user/edit-timezone')->with([
                'timezone_class' => 'has-error',
                'timezone_error' => 'A timezone is required.'
           ]);
        }

        $user = User::find(auth()->user()->id);
        $user->timezone = $timezone;
        $user->save();

        return redirect('/user/view')->with('is_edited_timezone', true);
    }

    /**
     * @summary Responds to HTTP POST /user/remove. Deletes logged-in user from database.
     *
     * @return Response
     */
    public function getRemove()
    {
        $user = User::find(auth()->user()->id);
        $user->delete();

        return redirect('/');
    }

    /**
     * @summary Responds to HTTP GET /user/links. Displays a list of all logged-in user's links.
     *
     * @return Response
     */
    public function getLinks()
    {
        $user = User::find(auth()->user()->id);

        $urls = $user
            ->urls()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        $urls_with_hits = UrlHelper::changeTimeZone($urls);

        return view('links')->with('urls', $urls_with_hits);
    }

    /**
     * @summary Responds to HTTP GET /user/link/{url_id}. Displays, for a given url_id, a list of click
     * details: referer, click date.
     *
     * @return Response
     */
    public function getLink($url_id)
    {
        $url_object = Url::find($url_id);
        $url_hits = $url_object->urlHits;
        $url = $url_object->toArray();

        $db_time_zone = new DateTimeZone('UTC');
        $user_timezone = new DateTimeZone(auth()->user()->timezone);

        $created_at = new DateTime($url['created_at'], $db_time_zone);
        $created_at->setTimeZone($user_timezone);
        $url['created_at'] = $created_at->format('Y-m-d H:i:s');

        $url['hits'] = [];
        foreach ($url_hits as $url_hit) {
            $url_hit_created_at = new DateTime($url_hit['created_at'], $db_time_zone);
            $url_hit_created_at->setTimeZone($user_timezone);
            $url_hit['created_at'] = $url_hit_created_at->format('Y-m-d H:i:s');

            $url['hits'][] = $url_hit;
        }

        return view('clicks')->with('url', $url);
    }
}
