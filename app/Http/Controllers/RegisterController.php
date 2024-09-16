<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function list()
{
    // Fetch only employees (if you want to list only employees)
    $employees = User::where('role', 'employee')->get();

    // Or if you want to list all users including admins
    // $employees = User::all();

    return view('user.list', compact('employees'));
}
    public function showRegistrationForm()
    {
        // Only allow admin users to see the registration form
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('user.register');
    }

    /**
     * Handle an employee registration request.
     */
    public function register(Request $request)
    {
        // Ensure only admins can register new employees
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Validate the registration form data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create a new employee user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee', // Automatically assign 'employee' role
        ]);

        // Optionally redirect the admin back with success message
        return redirect()->route('employees.index')->with('status', 'Employee registered successfully.');
    }
    public function edit($id)
{
    $employee = User::findOrFail($id);

    // Only allow admin to edit employees
    if (Auth::user()->role !== 'admin') {
        abort(403, 'Unauthorized action.');
    }

    return view('user.edit', compact('employee'));
}

public function update(Request $request, $id)
{
    $employee = User::findOrFail($id);

    // Only allow admin to update employees
    if (Auth::user()->role !== 'admin') {
        abort(403, 'Unauthorized action.');
    }

    // Validate the update form data
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    // Update the employee's data
    $employee->name = $request->name;
    $employee->email = $request->email;

    if ($request->filled('password')) {
        $employee->password = Hash::make($request->password);
    }

    $employee->save();

    return redirect()->route('employees.index')->with('status', 'Employee updated successfully.');
}
public function destroy($id)
{
    $employee = User::findOrFail($id);

    // Only allow admin to delete employees
    if (Auth::user()->role !== 'admin') {
        abort(403, 'Unauthorized action.');
    }

    $employee->delete();

    return redirect()->route('employees.index')->with('status', 'Employee deleted successfully.');
}

}
