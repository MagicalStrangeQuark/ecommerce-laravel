<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    /** @var string */
    private const INVALID_CREDENTIALS_MESSAGE = 'Sorry, the email or password you entered is incorrect. Please try again.';

    /** @var string */
    private const INVALID_IMAGE_UPLOAD = "No image registered";

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(\Illuminate\Http\Request $request)
    {
        return view('index.listing', [
            'view' => \App\Helpers\Utils::main(Self::class, new \App\Models\User())
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create', [
            'view' => \App\Helpers\Utils::important(Self::class, \App\Helpers\Utils::CREATE, (object) [])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|unique:users|max:255|unique:users',
            'email' => 'required|max:255|unique:users',
            'first-password' => 'required|max:255',
            'second-password' => 'required|max:255'
        ]);

        if (
            $validator->fails()
        ) {
            return redirect()->route('User.create')->withErrors($validator)->withInput();
        }

        if (
            (string) $request->{"second-password"} !== (string) $request->{"first-password"}
        ) {
            return redirect()->route('User.create')->withErrors(['password' => 'Passwords entered do not match.']);
        }

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->{"first-password"})
        ]);

        return view('index.listing', [
            'view' => \App\Helpers\Utils::main(Self::class, new \App\Models\User())
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('components.box');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('users.edit', [
            'view' => $this->view($id)
        ]);
    }

    /**
     * View.
     * 
     * @param int $id
     * 
     * @return \stdClass
     */
    private function view(int $id): \stdClass
    {
        return \App\Helpers\Utils::important(Self::class, \App\Helpers\Utils::EDIT, (object) \App\Models\User::find($id)->toArray());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(\Illuminate\Http\Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => "required|max:255|unique:users,name,{$request->id}",
            'email' => "required|max:255|unique:users,email,{$request->id}"
        ]);

        if ($validator->fails()) {
            return redirect()->route('User.edit', [$id, 'view' => $this->view($id)])->withErrors($validator)->withInput();
        }

        $thereIsAlreadyARegisteredImage = (bool) \Illuminate\Support\Facades\Storage::disk('public')->exists(\App\Models\User::find($id)->image);

        if (is_null($request->file('image'))) {
            if (!$thereIsAlreadyARegisteredImage) {
                return redirect()->route('User.edit', [$id, 'view' => $this->view($id)])->withErrors([self::INVALID_IMAGE_UPLOAD]);
            } else {
                $image = \App\Models\User::find($id)->image;
            }
        } else {
            $image = $request->file('image')->store('users', 'public');
        }

        \App\Models\User::where('id', $id)->update([
            'email' => $request->email,
            'name' => $request->name,
            'image' => $image
        ]);

        if (((int) \App\Helpers\Utils::user()->id) === ((int) $request->id)) {
            \App\Helpers\Utils::update($id);
        }

        return view('index.listing', [
            'view' => \App\Helpers\Utils::main(Self::class, new \App\Models\User())
        ]);
    }

    /**
     * Download the user image.
     * 
     * @param int $id
     */
    public function download(int $id)
    {
        return \Illuminate\Support\Facades\Storage::download("public" . DIRECTORY_SEPARATOR . \App\Models\User::find($id)->image);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return \App\Helpers\Utils::JSONDestroyArray(true, $id, 'User');
    }

    /**
     * Validate an User.
     * 
     * @param \Illuminate\Http\Request $request
     */
    public function autenticate(\Illuminate\Http\Request $request)
    {
        $build = \App\Models\User::select(['id', 'name', 'email', 'password'])->where('email', $request->email);

        if ($build->count() > 0) {
            if ((bool) (\Illuminate\Support\Facades\Hash::check($request->password, $build->first()->password)) === true) {
                \App\Helpers\Utils::update($build->first()->id);

                return redirect()->route('app');
            }
        }

        return $this->login($request)->withErrors([self::INVALID_CREDENTIALS_MESSAGE]);
    }

    /**
     * Render an empty login form (Log In).
     * 
     * @param void
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(): \Illuminate\View\View
    {
        return view('login.login', [
            'view' => \App\Helpers\Utils::important(Self::class, \App\Helpers\Utils::LOGIN, (object) [])
        ]);
    }

    /**
     * Render an empty login form (Log Out).
     * 
     * @param void
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(): \Illuminate\Http\RedirectResponse
    {
        Session::flush();

        return redirect()->route('login');
    }
}
