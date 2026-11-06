
<template>
    <div class="table-container">

        <table>
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th v-if="canManage">Actions</th>
                </tr>
            </thead>

            <tbody>

                <tr
                    v-for="student in students"
                    :key="student.id"
                >
                    <td>{{ student.student_number }}</td>
                    <td>{{ student.firstname }}</td>
                    <td>{{ student.lastname }}</td>
                    <td>{{ student.email }}</td>
                    <td>{{ student.course }}</td>
                    <td>{{ student.year_level }}</td>

                    <td v-if="canManage" class="actions">

                        <button
                            class="edit-btn"
                            @click="emit('edit', student)"
                        >
                            Edit
                        </button>

                        <button
                            class="delete-btn"
                            @click="emit('delete', student.id)"
                        >
                            Delete
                        </button>

                    </td>
                </tr>

                <tr v-if="students.length === 0">
                    <td :colspan="canManage ? 7 : 6" class="empty">
                        No student records found.
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
    },

    canManage: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits([
    'edit',
    'delete'
]);

</script>

<style scoped>

.table-container {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    min-width: 750px;
}

th,
td {
    padding: 13px;
    border: 1px solid #e2e8f0;
    text-align: left;
}

th {
    background: #23418b;
    color: white;
}

tr:nth-child(even) {
    background: #f8fafc;
}

.actions {
    white-space: nowrap;
}

button {
    border: none;
    padding: 8px 12px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    margin-right: 5px;
}

.edit-btn {
    background: #2563eb;
}

.delete-btn {
    background: #dc2626;
}

.empty {
    text-align: center;
    padding: 25px;
    color: #64748b;
}

</style>
