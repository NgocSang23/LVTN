<template>
    <div class="container mt-4">
      <h3>Gán quyền giáo viên</h3>
      <table class="table table-bordered mt-3">
        <thead>
          <tr>
            <th>Tên</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.role }}</td>
            <td>
              <button class="btn btn-sm btn-success">Gán giáo viên</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>

  <script>
  export default {
    name: "AssignRole",
    data() {
      return {
        users: [
          { id: 1, name: "Lê Văn C", email: "c@example.com", role: "Học sinh" },
        ],
      };
    },
  };
  </script>
