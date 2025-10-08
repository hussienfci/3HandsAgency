<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    private function getUsers()
    {
        // Get users from session, or initialize with default data
        return Session::get('users', [
            ['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bob@example.com', 'status' => 'Active'],
            ['id' => 3, 'name' => 'Carol Davis', 'email' => 'carol@example.com', 'status' => 'Active'],
            ['id' => 4, 'name' => 'David Wilson', 'email' => 'david@example.com', 'status' => 'Active'],
            ['id' => 5, 'name' => 'Eva Brown', 'email' => 'eva@example.com', 'status' => 'Active'],
        ]);
    }

    private function saveUsers($users)
    {
        Session::put('users', $users);
        return $users;
    }

    public function index()
    {
        $users = $this->getUsers();
        return view('users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $users = $this->getUsers();
        
        // Generate new ID
        $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
        
        $newUser = [
            'id' => $newId,
            'name' => $request->name,
            'email' => $request->email,
            'status' => 'Active'
        ];
        
        $users[] = $newUser;
        $this->saveUsers($users);

        return response()->json([
            'success' => true,
            'user' => $newUser,
            'message' => 'User added successfully!'
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $users = $this->getUsers();
        $userFound = false;

        foreach ($users as &$user) {
            if ($user['id'] == $id) {
                $user['name'] = $request->name;
                $user['email'] = $request->email;
                $userFound = true;
                break;
            }
        }

        if (!$userFound) {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ], 404);
        }

        $this->saveUsers($users);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $users = $this->getUsers();
        $initialCount = count($users);
        
        $users = array_filter($users, function($user) use ($id) {
            return $user['id'] != $id;
        });

        if (count($users) === $initialCount) {
            return response()->json([
                'success' => false,
                'message' => 'User not found!'
            ], 404);
        }

        // Reindex array to maintain proper structure
        $users = array_values($users);
        $this->saveUsers($users);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer'
        ]);

        $userIds = $request->user_ids;
        $users = $this->getUsers();
        $initialCount = count($users);
        
        $users = array_filter($users, function($user) use ($userIds) {
            return !in_array($user['id'], $userIds);
        });

        if (count($users) === $initialCount) {
            return response()->json([
                'success' => false,
                'message' => 'No users found to delete!'
            ], 404);
        }

        // Reindex array to maintain proper structure
        $users = array_values($users);
        $this->saveUsers($users);

        $deletedCount = $initialCount - count($users);

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' user(s) deleted successfully!'
        ]);
    }

    // API endpoint to get users data (for AJAX calls)
    public function apiIndex()
    {
        $users = $this->getUsers();
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}