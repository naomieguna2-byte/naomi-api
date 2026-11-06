<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    // GET: Display all students
    public function index()
    {
        $students = Student::all();

        return response()->json([
            'message' => 'Student records retrieved successfully.',
            'data' => $students
        ], 200);
    }

    // GET: Display one student
    public function show($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Student retrieved successfully.',
            'data' => $student
        ], 200);
    }

    // POST: Create a student
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'course' => 'required|string|max:255',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student created successfully.',
            'data' => $student
        ], 201);
    }

    // PUT: Update a student
    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
            'course' => 'required|string|max:255',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully.',
            'data' => $student
        ], 200);
    }

    // DELETE: Remove a student
    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully.'
        ], 200);
    }
}
