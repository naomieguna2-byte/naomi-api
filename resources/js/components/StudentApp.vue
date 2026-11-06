
<template>
    <div class="container">

        <header class="header">
            <div>
                <h1>🎓 Student Management System</h1>
                <p>Laravel REST API + Vue.js 3</p>
            </div>

            <span class="status" :class="{ connected: connected }">
                {{ connected ? '● Connected' : '● Disconnected' }}
            </span>
        </header>

        <!-- API AUTHENTICATION -->
        <section class="token-section">
            <h2>API Authentication</h2>

            <div class="token-row">
                <input
                    v-model="token"
                    type="password"
                    placeholder="Paste your admin Bearer token"
                />

                <button @click="fetchStudents" :disabled="loading">
                    {{ loading ? 'Connecting...' : 'Connect API' }}
                </button>
            </div>
        </section>

        <!-- MESSAGE -->
        <div v-if="message" class="alert" :class="messageType">
            {{ message }}

            <button class="close-btn" @click="message = ''">
                ×
            </button>
        </div>

        <!-- STUDENT FORM -->
        <section class="form-section">

            <div class="section-heading">
                <h2>
                    {{ editing ? 'Edit Student' : 'Add New Student' }}
                </h2>

                <button
                    v-if="editing"
                    class="cancel-btn"
                    @click="resetForm"
                >
                    Cancel Edit
                </button>
            </div>

            <form @submit.prevent="saveStudent">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Student Number</label>
                        <input
                            v-model="form.student_number"
                            placeholder="Enter student number"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>First Name</label>
                        <input
                            v-model="form.firstname"
                            placeholder="Enter first name"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>Middle Name</label>
                        <input
                            v-model="form.middlename"
                            placeholder="Enter middle name"
                        />
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input
                            v-model="form.lastname"
                            placeholder="Enter last name"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Enter email address"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>Course</label>
                        <input
                            v-model="form.course"
                            placeholder="Example: BSIT"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>Year Level</label>

                        <select v-model.number="form.year_level" required>
                            <option value="" disabled>
                                Select year level
                            </option>
                            <option :value="1">1st Year</option>
                            <option :value="2">2nd Year</option>
                            <option :value="3">3rd Year</option>
                            <option :value="4">4th Year</option>
                            <option :value="5">5th Year</option>
                            <option :value="6">6th Year</option>
                        </select>
                    </div>

                </div>

                <button
                    type="submit"
                    class="submit-btn"
                    :disabled="saving || !connected"
                >
                    {{
                        saving
                            ? 'Saving...'
                            : editing
                                ? 'Update Student'
                                : 'Add Student'
                    }}
                </button>

            </form>
        </section>

        <!-- STUDENT TABLE -->
        <section class="table-section">

            <div class="section-heading">
                <div>
                    <h2>Student Records</h2>
                    <p>Total Students: {{ students.length }}</p>
                </div>

                <button
                    class="refresh-btn"
                    @click="fetchStudents"
                    :disabled="loading"
                >
                    ↻ Refresh
                </button>
            </div>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Number</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr
                            v-for="student in students"
                            :key="student.id"
                        >
                            <td>{{ student.id }}</td>

                            <td>{{ student.student_number }}</td>

                            <td>
                                {{ student.firstname }}
                                {{ student.middlename || '' }}
                                {{ student.lastname }}
                            </td>

                            <td>{{ student.email }}</td>

                            <td>{{ student.course }}</td>

                            <td>{{ student.year_level }}</td>

                            <td class="actions">

                                <button
                                    class="edit-btn"
                                    @click="editStudent(student)"
                                >
                                    Edit
                                </button>

                                <button
                                    class="delete-btn"
                                    @click="deleteStudent(student.id)"
                                >
                                    Delete
                                </button>

                            </td>
                        </tr>

                        <tr v-if="students.length === 0">
                            <td colspan="7" class="empty">
                                No student records found.
                                Connect your API to load students.
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>
        </section>

        <footer>
            Student Management System | Laravel 13 + Vue.js 3
        </footer>

    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const API_URL = '/api/students';

const token = ref('');
const students = ref([]);

const loading = ref(false);
const saving = ref(false);
const connected = ref(false);
const editing = ref(false);
const editingId = ref(null);

const message = ref('');
const messageType = ref('success');

const form = reactive({
    student_number: '',
    firstname: '',
    middlename: '',
    lastname: '',
    email: '',
    course: '',
    year_level: ''
});

const api = axios.create({
    baseURL: '/',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
    }
});

// MESSAGE
function showMessage(text, type = 'success') {
    message.value = text;
    messageType.value = type;
}

// ERROR HANDLING
function getError(error) {
    if (error.response?.data?.errors) {
        return Object.values(error.response.data.errors)
            .flat()
            .join('\n');
    }

    return error.response?.data?.message
        || error.message
        || 'Something went wrong.';
}

// AUTHORIZATION
function getHeaders() {
    return {
        Authorization: `Bearer ${token.value.trim()}`
    };
}

// GET STUDENTS
async function fetchStudents() {

    if (!token.value.trim()) {
        showMessage('Please enter your admin API token.', 'error');
        return;
    }

    loading.value = true;

    try {
        const response = await api.get(API_URL, {
            headers: getHeaders()
        });

        const data = response.data;

        if (Array.isArray(data)) {
            students.value = data;
        } else if (Array.isArray(data.data)) {
            students.value = data.data;
        } else if (Array.isArray(data.students)) {
            students.value = data.students;
        } else {
            students.value = [];
        }

        connected.value = true;

        showMessage(
            `Connected successfully! ${students.value.length} student(s) loaded.`,
            'success'
        );

    } catch (error) {
        connected.value = false;
        students.value = [];

        showMessage(getError(error), 'error');

    } finally {
        loading.value = false;
    }
}

// RESET FORM
function resetForm() {

    Object.assign(form, {
        student_number: '',
        firstname: '',
        middlename: '',
        lastname: '',
        email: '',
        course: '',
        year_level: ''
    });

    editing.value = false;
    editingId.value = null;
}

// EDIT STUDENT
function editStudent(student) {

    editingId.value = student.id;

    Object.assign(form, {
        student_number: student.student_number || '',
        firstname: student.firstname || '',
        middlename: student.middlename || '',
        lastname: student.lastname || '',
        email: student.email || '',
        course: student.course || '',
        year_level: student.year_level || ''
    });

    editing.value = true;

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ADD OR UPDATE STUDENT
async function saveStudent() {

    if (!connected.value || !token.value.trim()) {
        showMessage('Please connect to the API first.', 'error');
        return;
    }

    saving.value = true;

    try {

        const studentData = { ...form };

        if (editing.value) {

            await api.put(
                `${API_URL}/${editingId.value}`,
                studentData,
                { headers: getHeaders() }
            );

            showMessage('Student updated successfully!', 'success');

        } else {

            await api.post(
                API_URL,
                studentData,
                { headers: getHeaders() }
            );

            showMessage('Student added successfully!', 'success');
        }

        resetForm();

        await fetchStudents();

    } catch (error) {

        showMessage(getError(error), 'error');

    } finally {
        saving.value = false;
    }
}

// DELETE STUDENT
async function deleteStudent(id) {

    if (!confirm('Are you sure you want to delete this student?')) {
        return;
    }

    try {

        await api.delete(`${API_URL}/${id}`, {
            headers: getHeaders()
        });

        showMessage('Student deleted successfully!', 'success');

        await fetchStudents();

    } catch (error) {

        showMessage(getError(error), 'error');
    }
}
</script>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
}

.container {
    max-width: 1250px;
    margin: 35px auto;
    padding: 25px;
}

.header {
    background: #1e3a8a;
    color: white;
    padding: 25px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 8px;
}

.header p {
    margin: 0;
    color: #dbeafe;
}

.status {
    background: #991b1b;
    padding: 10px 15px;
    border-radius: 20px;
    font-size: 13px;
}

.status.connected {
    background: #15803d;
}

.token-section,
.form-section,
.table-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

h2 {
    margin-top: 0;
}

.token-row {
    display: flex;
    gap: 12px;
}

input,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 14px;
    outline: none;
}

input:focus,
select:focus {
    border-color: #2563eb;
}

button {
    border: none;
    padding: 11px 17px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
    color: white;
    background: #2563eb;
}

button:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.token-row button {
    white-space: nowrap;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    white-space: pre-line;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.close-btn {
    float: right;
    background: transparent;
    color: inherit;
    padding: 0 5px;
    font-size: 20px;
}

.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-heading p {
    color: #64748b;
    margin-bottom: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    font-size: 14px;
}

.submit-btn {
    margin-top: 22px;
    background: #15803d;
}

.cancel-btn {
    background: #64748b;
}

.refresh-btn {
    background: #0f766e;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

th, td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

th {
    background: #eff6ff;
    color: #1e3a8a;
}

.actions {
    display: flex;
    gap: 7px;
}

.edit-btn {
    background: #d97706;
}

.delete-btn {
    background: #dc2626;
}

.empty {
    text-align: center;
    color: #64748b;
    padding: 30px;
}

footer {
    text-align: center;
    color: #64748b;
    padding: 20px;
}

@media (max-width: 650px) {
    .container {
        padding: 12px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .token-row {
        flex-direction: column;
    }
}
</style>
