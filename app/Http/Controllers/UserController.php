<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(5);
        return response()->json($users);
    }

    public function store(Request $request)
{
    // Validate incoming request
    $request->validate([
        'name' => 'required|string|max:255',
        'gmail' => 'required|email|unique:users',
        'photo' => 'nullable|image|mimes:jpg,png,jpeg,gif',
        'photoname' => 'nullable|string', 
        'dob' => 'required|date',
    ]);

    $photoPath = null;
    if ($request->hasFile('photo')) {
        $filename = $request->input('photoname', 'default') ;
        $photoPath = $request->file('photo')->storeAs('photos', $filename, 'public');
    }
    $user = User::create([
        'name' => $request->name,
        'gmail' => $request->gmail,
        'photo' => $photoPath,
        'dob' => $request->dob,
    ]);
    return response()->json($user, 201);
}


public function update(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'gmail' => 'required|email|unique:users,gmail,' . $request->id,
        'photo' => 'nullable|image|mimes:jpg,png,jpeg,gif',
        'photoname' => 'nullable|string', 
        'dob' => 'required|date',
    ]);

    $user = User::findOrFail($request->id);

    $user->name = $request->name;
    $user->gmail = $request->gmail;
    $user->dob = $request->dob;

    if ($request->hasFile('photo')) {
        if ($user->photo) {
            Storage::delete('public/' . $user->photo);
        }
        $extension = $request->file('photo')->getClientOriginalExtension();
        $filename = $request->input('photoname', 'photo');
        $photoPath = $request->file('photo')->storeAs('photos', $filename, 'public');
        $user->photo = $photoPath;
    }
    $user->save();
    return response()->json($user);
}



    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->photo) {
            Storage::delete('public/' . $user->photo);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
