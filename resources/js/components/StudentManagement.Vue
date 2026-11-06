
<template>
    <div class="container">

        <!-- HEADER -->
        <header class="header">
            <div>
                <h1>🎓 Student Management System</h1>
                <p>Laravel REST API + Vue.js 3</p>
            </div>

            <div v-if="currentUser" class="user-info">
                <div>
                    <strong>{{ currentUser.name }}</strong>
                    <span class="role">{{ roleName }}</span>
                </div>

                <button
                    class="logout-btn"
                    @click="logout"
                    :disabled="loading"
                >
                    Logout
                </button>
            </div>
        </header>

        <!-- LOGIN -->
        <section v-if="!currentUser" class="card login-card">

            <h2>Account Login</h2>
            <p>Login to access the Student Management System.</p>

            <form @submit.prevent="login">

                <label>Email Address</label>
                <input
                    type="email"
                    v-model.trim="loginForm.email"
                    placeholder="Enter your email"
                    required
                >

                <label>Password</label>
                <input
                    type="password"
                    v-model="loginForm.password"
                    placeholder="Enter your password"
                    required
                >

                <button type="submit" :disabled="loading">
                    {{ loading ? 'Logging in...' : 'Login' }}
                </button>

            </form>

            <div v-if="errorMessage" class="error">
                {{ errorMessage }}
            </div>

        </section>

        <!-- AUTHENTICATED CONTENT -->
        <template v-else>

            <!-- SUCCESS MESSAGE -->
            <div v-if="successMessage" class="success">
                {{ successMessage }}
            </div>

            <!-- ERROR MESSAGE -->
            <div v-if="errorMessage" class="error">
                {{ errorMessage }}
            </div>

            <!-- ADMIN STUDENT FORM -->
            <section v-if="isAdmin" class="card">

                <h2>
                    {{ editing ? 'Update Student' : 'Add New Student' }}
                </h2>

                <form @submit.prevent="saveStudent">

                    <div class="form-grid">

                        <div>
                            <label>Student Number</label>
                            <input
                                v-model.trim="studentForm.student_number"
                                placeholder="Enter student number"
                                required
                            >
                        </div>

                        <div>
                            <label>First Name</label>
                            <input
                                v-model.trim="studentForm.firstname"
                                placeholder="Enter first name"
                                required
                            >
                        </div>

                        <div>
                            <label>Middle Name</label>
                            <input
                                v-model.trim="studentForm.middlename"
                                placeholder="Enter middle name"
                            >
                        </div>

                        <div>
                            <label>Last Name</label>
                            <input
                                v-model.trim="studentForm.lastname"
                                placeholder="Enter last name"
                                required
                            >
                        </div>

                        <div>
                            <label>Email Address</label>
                            <input
                                type="email"
                                v-model.trim="studentForm.email"
                                placeholder="Enter email"
                                required
                            >
                        </div>

                        <div>
                            <label>Course</label>
                            <input
                                v-model.trim="studentForm.course"
                                placeholder="Example: BSIT"
                                required
                            >
                        </div>

                        <div>
                            <label>Year Level</label>
                            <select
                                v-model.number="studentForm.year_level"
                                required
                            >
                                <option disabled value="">
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

                    <!-- VALIDATION ERRORS -->
                    <div v-if="validationErrors.length" class="error">
                        <strong>Please correct the following:</strong>

                        <ul>
                            <li
                                v-for="(error, index) in validationErrors"
                                :key="index"
                            >
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <button type="submit" :disabled="loading">
                        {{
                            loading
                                ? 'Please wait...'
                                : editing
                                    ? 'Update Student'
                                    : 'Add Student'
                        }}
                    </button>

                    <button
                        v-if="editing"
                        type="button"
                        class="cancel-btn"
                        @click="resetForm"
                    >
                        Cancel
                    </button>

                </form>
            </section>

            <!-- REGULAR USER NOTICE -->
            <div v-else class="notice">
                <strong>Regular User Access</strong>
                <p>
                    You can view student records, but you cannot
                    add, edit, or delete students.
                </p>
            </div>

            <!-- STUDENT RECORDS -->
            <section class="card">

                <div class="table-heading">
                    <div>
                        <h2>Student Records</h2>
                        <p>Total Students: {{ students.length }}</p>
                    </div>

                    <button
                        class="refresh-btn"
                        @click="loadStudents"
                        :disabled="loadingStudents"
                    >
                        {{ loadingStudents ? 'Loading...' : 'Refresh' }}
                    </button>
                </div>

                <div v-if="loadingStudents" class="loading">
                    Loading student records...
                </div>

                <StudentTable
                    v-else
                    :students="students"
                    :can-manage="isAdmin"
                    @edit="editStudent"
                    @delete="deleteStudent"
                />

            </section>

        </template>

        <footer>
            Student Management System | Laravel 13 + Vue.js 3
        </footer>

    </div>
</template>

<script setup>

import { ref, reactive, computed, onMounted } from 'vue';
import StudentTable from './StudentTable.vue';

// =====================================
// 1. APPLICATION STATE
// =====================================

const students = ref([]);

const currentUser = ref(null);

const token = ref(localStorage.getItem('token') || '');

const loading = ref(false);

const loadingStudents = ref(false);

const editing = ref(false);

const editingId = ref(null);

const successMessage = ref('');

const errorMessage = ref('');

const validationErrors = ref([]);


// =====================================
// 2. LOGIN FORM STATE
// =====================================

const loginForm = reactive({
    email: '',
    password: ''
});


// =====================================
// 3. STUDENT FORM STATE
// =====================================

const emptyForm = () => ({
    student_number: '',
    firstname: '',
    middlename: '',
    lastname: '',
    email: '',
    course: '',
    year_level: ''
});

const studentForm = reactive(emptyForm());


// =====================================
// 4. ROLE MANAGEMENT
// =====================================

const isAdmin = computed(() => {
    const role = currentUser.value?.role?.toLowerCase();

    return role === 'admin' || role === 'administrator';
});

const roleName = computed(() => {
    return isAdmin.value
        ? 'Administrator'
        : 'Regular User';
});


// =====================================
// 5. API REQUEST WITH BEARER TOKEN
// =====================================

async function apiRequest(url, options = {}) {

    const headers = {
        'Accept': 'application/json',
        ...options.headers
    };

    if (options.body) {
        headers['Content-Type'] = 'application/json';
    }

    if (token.value) {
        headers['Authorization'] = `Bearer ${token.value}`;
    }

    const response = await fetch(url, {
        ...options,
        headers
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {

        const error = new Error(
            data.message || 'Something went wrong.'
        );

        error.status = response.status;
        error.data = data;

        throw error;
    }

    return data;
}


// =====================================
// 6. LOGIN
// =====================================

async function login() {

    loading.value = true;

    errorMessage.value = '';
    successMessage.value = '';
    validationErrors.value = [];

    try {

        const data = await apiRequest('/api/login', {
            method: 'POST',
            body: JSON.stringify(loginForm)
        });

        if (!data.token || !data.user) {
            throw new Error('Invalid login response from server.');
        }

        // SAVE SANCTUM TOKEN
        token.value = data.token;

        localStorage.setItem('token', data.token);

        // SAVE AUTHENTICATED USER
        currentUser.value = data.user;

        // CLEAR PASSWORD
        loginForm.password = '';

        successMessage.value = 'Login successful! Welcome.';

        await loadStudents();

    } catch (error) {

        errorMessage.value =
            error.data?.message ||
            error.message ||
            'Unable to login. Please try again.';

        validationErrors.value =
            Object.values(error.data?.errors || {}).flat();

    } finally {

        loading.value = false;

    }
}


// =====================================
// 7. GET AUTHENTICATED USER
// =====================================

async function getCurrentUser() {

    try {

        const data = await apiRequest('/api/me');

        currentUser.value = data.user || data;

        await loadStudents();

    } catch (error) {

        clearSession();

    }
}


// =====================================
// 8. LOAD STUDENTS
// =====================================

async function loadStudents() {

    loadingStudents.value = true;

    errorMessage.value = '';

    try {

        const data = await apiRequest('/api/students');

        students.value = Array.isArray(data)
            ? data
            : (data.data || []);

    } catch (error) {

        errorMessage.value =
            error.data?.message ||
            'Unable to retrieve student records.';

        if (error.status === 401) {
            clearSession();
        }

    } finally {

        loadingStudents.value = false;

    }
}


// =====================================
// 9. ADD OR UPDATE STUDENT
// =====================================

async function saveStudent() {

    if (!isAdmin.value) {

        errorMessage.value =
            'You are not authorized to manage students.';

        return;
    }

    loading.value = true;

    errorMessage.value = '';
    successMessage.value = '';
    validationErrors.value = [];

    try {

        const studentData = {
            student_number: studentForm.student_number,
            firstname: studentForm.firstname,
            middlename: studentForm.middlename || null,
            lastname: studentForm.lastname,
            email: studentForm.email,
            course: studentForm.course,
            year_level: studentForm.year_level
        };

        const url = editing.value
            ? `/api/students/${editingId.value}`
            : '/api/students';

        const method = editing.value ? 'PUT' : 'POST';

        await apiRequest(url, {
            method,
            body: JSON.stringify(studentData)
        });

        successMessage.value = editing.value
            ? 'Student updated successfully!'
            : 'Student added successfully!';

        resetForm();

        await loadStudents();

    } catch (error) {

        if (error.status === 422) {

            validationErrors.value =
                Object.values(error.data?.errors || {}).flat();

            errorMessage.value =
                'Please check the student information.';

        } else {

            errorMessage.value =
                error.data?.message || 'Unable to save student.';

        }

        if (error.status === 401) {
            clearSession();
        }

    } finally {

        loading.value = false;

    }
}


// =====================================
// 10. EDIT STUDENT
// =====================================

function editStudent(student) {

    if (!isAdmin.value) return;

    Object.assign(studentForm, {
        student_number: student.student_number ?? '',
        firstname: student.firstname ?? '',
        middlename: student.middlename ?? '',
        lastname: student.lastname ?? '',
        email: student.email ?? '',
        course: student.course ?? '',
        year_level: Number(student.year_level)
    });

    editingId.value = student.id;

    editing.value = true;

    successMessage.value = '';
    errorMessage.value = '';
    validationErrors.value = [];

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

}


// =====================================
// 11. DELETE STUDENT
// =====================================

async function deleteStudent(id) {

    if (!isAdmin.value) return;

    const confirmed = window.confirm(
        'Are you sure you want to delete this student?'
    );

    if (!confirmed) return;

    loading.value = true;

    errorMessage.value = '';
    successMessage.value = '';

    try {

        await apiRequest(`/api/students/${id}`, {
            method: 'DELETE'
        });

        successMessage.value =
            'Student deleted successfully!';

        await loadStudents();

    } catch (error) {

        errorMessage.value =
            error.data?.message || 'Unable to delete student.';

        if (error.status === 401) {
            clearSession();
        }

    } finally {

        loading.value = false;

    }
}


// =====================================
// 12. RESET STUDENT FORM
// =====================================

function resetForm() {

    Object.assign(studentForm, emptyForm());

    editing.value = false;

    editingId.value = null;

    validationErrors.value = [];

}


// =====================================
// 13. CLEAR AUTHENTICATION SESSION
// =====================================

function clearSession() {

    token.value = '';

    currentUser.value = null;

    students.value = [];

    localStorage.removeItem('token');

    resetForm();

}


// =====================================
// 14. LOGOUT
// =====================================

async function logout() {

    loading.value = true;

    try {

        await apiRequest('/api/logout', {
            method: 'POST'
        });

        clearSession();

        successMessage.value = '';
        errorMessage.value = '';

    } catch (error) {

        errorMessage.value =
            error.data?.message || 'Unable to logout.';

    } finally {

        loading.value = false;

    }
}


// =====================================
// 15. RESTORE LOGIN SESSION
// =====================================

onMounted(() => {

    if (token.value) {
        getCurrentUser();
    }

});

</script>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f1f5f9;
    font-family: Arial, sans-serif;
    color: #1e293b;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

.header {
    background: #23418b;
    color: white;
    padding: 30px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.header h1 {
    margin: 0 0 10px;
}

.header p {
    margin: 0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-info div {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.role {
    background: #dbeafe;
    color: #1e40af;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 13px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px #0000000d;
}

.login-card {
    max-width: 500px;
    margin: 50px auto;
}

label {
    display: block;
    font-weight: bold;
    margin: 15px 0 8px;
}

input, select {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
}

input:focus, select:focus {
    outline: 2px solid #93c5fd;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 20px;
}

button {
    border: none;
    padding: 12px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 15px;
    background: #15803d;
    color: white;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.logout-btn {
    background: #dc2626;
}

.cancel-btn {
    background: #64748b;
    margin-left: 8px;
}

.refresh-btn {
    background: #0f766e;
}

.success {
    background: #dcfce7;
    color: #166534;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 7px;
}

.error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 15px;
    margin-top: 15px;
    border-radius: 7px;
}

.notice {
    background: #dbeafe;
    color: #1e40af;
    padding: 18px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.loading {
    padding: 20px;
    text-align: center;
}

.table-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

footer {
    text-align: center;
    padding: 25px;
    color: #64748b;
}

@media (max-width: 700px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
    }

    .table-heading {
        flex-direction: column;
        align-items: flex-start;
    }

}
</style>
