
<template>
    <div class="container">

        <!-- Header -->
        <header class="header">
            <div>
                <h1>🎓 Student Management System</h1>
                <p>Laravel REST API + Vue.js 3</p>
            </div>

            <span class="status">● Connected</span>
        </header>

        <!-- Success and Error Messages -->
        <div v-if="message"
             :class="['alert', messageType]">
            {{ message }}

            <button @click="message = ''">×</button>
        </div>

        <!-- Add / Edit Student Form -->
        <section class="card">

            <div class="section-heading">
                <h2>
                    {{ editing ? 'Edit Student' : 'Add New Student' }}
                </h2>

                <button v-if="editing"
                        class="cancel-btn"
                        @click="resetForm">
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
                        >
                    </div>

                    <div class="form-group">
                        <label>First Name</label>
                        <input
                            v-model="form.firstname"
                            placeholder="Enter first name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Middle Name</label>
                        <input
                            v-model="form.middlename"
                            placeholder="Enter middle name"
                        >
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input
                            v-model="form.lastname"
                            placeholder="Enter last name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input
                            type="email"
                            v-model="form.email"
                            placeholder="Enter email address"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Course</label>
                        <input
                            v-model="form.course"
                            placeholder="Example: BSIT"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Year Level</label>
                        <select v-model="form.year_level" required>
                            <option value="">Select year level</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>

                </div>

                <!-- Validation Errors -->
                <div v-if="Object.keys(errors).length"
                     class="validation">
                    <p v-for="(error, key) in errors" :key="key">
                        {{ error[0] }}
                    </p>
                </div>

                <button type="submit"
                        class="submit-btn"
                        :disabled="saving">

                    {{
                        saving
                            ? 'Please wait...'
                            : editing
                                ? 'Update Student'
                                : 'Add Student'
                    }}

                </button>

            </form>
        </section>

        <!-- Student Records -->
        <section class="card">

            <div class="section-heading">
                <div>
                    <h2>Student Records</h2>
                    <p class="total">
                        Total Students: {{ students.length }}
                    </p>
                </div>

                <button class="refresh-btn" @click="loadStudents">
                    ↻ Refresh
                </button>
            </div>

            <p v-if="loading" class="loading">
                Loading student records...
            </p>

            <StudentTable
                v-else
                :students="students"
                @edit="editStudent"
                @delete="deleteStudent"
            />

        </section>

        <footer>
            Student Management System | Laravel 13 + Vue.js 3
        </footer>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import StudentTable from './StudentTable.vue';

// Laravel API endpoint
const API_URL = '/api/students';

// Reactive data
const students = ref([]);
const loading = ref(false);
const saving = ref(false);
const editing = ref(false);
const editId = ref(null);

const message = ref('');
const messageType = ref('success');
const errors = ref({});

// Empty form
const emptyForm = () => ({
    student_number: '',
    firstname: '',
    middlename: '',
    lastname: '',
    email: '',
    course: '',
    year_level: ''
});

const form = reactive(emptyForm());

// Show success or error message
function showMessage(text, type = 'success') {
    message.value = text;
    messageType.value = type;
}

// GET - Retrieve all students
async function loadStudents() {
    loading.value = true;

    try {
        const response = await fetch(API_URL, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to retrieve student records.');
        }

        const data = await response.json();

        students.value = Array.isArray(data)
            ? data
            : (data.data || []);

    } catch (error) {
        showMessage(error.message, 'error');
    } finally {
        loading.value = false;
    }
}

// POST - Add student
// PUT - Update student
async function saveStudent() {
    saving.value = true;
    errors.value = {};

    try {
        const url = editing.value
            ? `${API_URL}/${editId.value}`
            : API_URL;

        const method = editing.value ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(form)
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                errors.value = data.errors || {};
                throw new Error(
                    'Please check the information you entered.'
                );
            }

            throw new Error(data.message || 'Operation failed.');
        }

        showMessage(
            editing.value
                ? 'Student updated successfully!'
                : 'Student added successfully!'
        );

        resetForm();
        await loadStudents();

    } catch (error) {
        showMessage(error.message, 'error');
    } finally {
        saving.value = false;
    }
}

// Edit student
function editStudent(student) {
    editing.value = true;
    editId.value = student.id;

    Object.assign(form, {
        student_number: student.student_number || '',
        firstname: student.firstname || '',
        middlename: student.middlename || '',
        lastname: student.lastname || '',
        email: student.email || '',
        course: student.course || '',
        year_level: String(student.year_level || '')
    });

    errors.value = {};

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// DELETE - Remove student
async function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student?')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Unable to delete student.');
        }

        showMessage('Student deleted successfully!');

        if (editing.value && editId.value === id) {
            resetForm();
        }

        await loadStudents();

    } catch (error) {
        showMessage(error.message, 'error');
    }
}

// Reset form
function resetForm() {
    Object.assign(form, emptyForm());

    editing.value = false;
    editId.value = null;
    errors.value = {};
}

// Automatically retrieve records when page opens
onMounted(() => {
    loadStudents();
});
</script>

<style scoped>
* {
    box-sizing: border-box;
}

.container {
    max-width: 1300px;
    margin: 40px auto;
    padding: 20px;
    font-family: Arial, sans-serif;
    color: #253047;
}

.header {
    background: #203e8b;
    color: white;
    padding: 30px;
    border-radius: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 10px;
    font-size: 32px;
}

.header p {
    margin: 0;
}

.status {
    background: #15803d;
    padding: 12px 20px;
    border-radius: 30px;
    white-space: nowrap;
}

.card {
    background: white;
    padding: 27px;
    border-radius: 14px;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,.07);
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 25px;
}

.section-heading h2 {
    margin: 0;
    font-size: 25px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.form-group label {
    font-weight: bold;
}

input, select {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
}

input:focus, select:focus {
    outline: 2px solid #2563eb;
}

button {
    border: none;
    border-radius: 7px;
    padding: 12px 18px;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

.submit-btn {
    background: #15803d;
    margin-top: 24px;
}

.submit-btn:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.cancel-btn {
    background: #64748b;
}

.refresh-btn {
    background: #087e77;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
}

.alert.success {
    background: #dcfce7;
    color: #166534;
}

.alert.error {
    background: #fee2e2;
    color: #991b1b;
}

.alert button {
    background: transparent;
    color: inherit;
    font-size: 20px;
    padding: 0 5px;
}

.validation {
    color: #b91c1c;
    background: #fee2e2;
    padding: 10px 20px;
    margin-top: 15px;
    border-radius: 7px;
}

.validation p {
    margin: 5px 0;
}

.total {
    color: #64748b;
}

.loading {
    text-align: center;
    padding: 25px;
}

footer {
    text-align: center;
    color: #64748b;
    padding: 25px;
}

@media (max-width: 700px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .header h1 {
        font-size: 24px;
    }
}
</style>
