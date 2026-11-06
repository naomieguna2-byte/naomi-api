<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    // READ - Get all students
    public function index()
    {
        return response()->json(
            Student::orderBy('id', 'desc')->get()
        );
    }

    // CREATE - Add a student
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:50|unique:students,student_number',
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'course' => 'required|string|max:100',
            'year_level' => 'required|integer|min:1|max:4',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student added successfully!',
            'student' => $student
        ], 201);
    }

    // READ - Get one student
    public function show($id)
    {
        $student = Student::findOrFail($id);

        return response()->json($student);
    }

    // UPDATE - Edit student
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')
                    ->ignore($student->id),
            ],
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'course' => 'required|string|max:100',
            'year_level' => 'required|integer|min:1|max:4',
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully!',
            'student' => $student
        ]);
    }

    // DELETE - Remove student
    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully!'
        ]);
    }
}
