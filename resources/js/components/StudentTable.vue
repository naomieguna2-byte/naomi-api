
<template>
    <div class="table-responsive">
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
                <tr v-if="students.length === 0">
                    <td colspan="7" class="empty">
                        No student records found.
                    </td>
                </tr>

                <tr v-for="student in students" :key="student.id">
                    <td>{{ student.id }}</td>
                    <td>{{ student.student_number }}</td>
                    <td>
                        {{ student.firstname }}
                        {{ student.middlename }}
                        {{ student.lastname }}
                    </td>
                    <td>{{ student.email }}</td>
                    <td>{{ student.course }}</td>
                    <td>{{ student.year_level }}</td>

                    <td>
                        <button class="edit-btn"
                            @click="$emit('edit', student)">
                            Edit
                        </button>

                        <button class="delete-btn"
                            @click="$emit('delete', student.id)">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    students: {
        type: Array,
        default: () => []
    }
});

defineEmits(['edit', 'delete']);
</script>

<style scoped>
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    min-width: 750px;
}

th {
    background: #eef4ff;
    color: #203e8b;
    padding: 15px;
    text-align: left;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.empty {
    text-align: center;
    color: #64748b;
    padding: 30px;
}

button {
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    font-weight: bold;
}

.edit-btn {
    background: #e58a00;
    margin-right: 5px;
}

.delete-btn {
    background: #dc2626;
}
</style>
