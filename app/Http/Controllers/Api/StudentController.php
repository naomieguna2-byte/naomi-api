<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    // GET ALL STUDENTS
    public function index()
    {
        return response()->json(
            Student::latest()->get()
        );
    }

    // GET STUDENT BY ID
    public function show($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        return response()->json($student);
    }

    // ADD STUDENT
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:50|unique:students,student_number',
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'course' => 'required|string|max:255',
            'year_level' => 'required|integer|min:1|max:6',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student added successfully!',
            'student' => $student
        ], 201);
    }

    // UPDATE STUDENT
    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found.'
            ], 404);
        }

        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')->ignore($student->id)
            ],
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')->ignore($student->id)
            ],
            'course' => 'required|string|max:255',
            'year_level' => 'required|integer|min:1|max:6',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully!',
            'student' => $student
        ]);
    }

    // DELETE STUDENT
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
            'message' => 'Student deleted successfully!'
        ]);
    }
}
