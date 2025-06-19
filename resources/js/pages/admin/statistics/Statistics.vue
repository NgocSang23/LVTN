<template>
    <div class="container mt-4">
      <h3>Phân tích sử dụng</h3>
      <ul class="list-group mt-3">
        <li class="list-group-item d-flex justify-content-between">
          Tổng số người dùng <span>{{ totalUsers }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          Số lượt ôn tập <span>{{ totalReviews }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          Số flashcard được tạo <span>{{ totalFlashcards }}</span>
        </li>
      </ul>
    </div>
  </template>

  <script>
  // Thống kê sử dụng
  export default {
    name: "Statistics",
    data() {
      return {
        totalUsers: 124,
        totalReviews: 540,
        totalFlashcards: 312,
      };
    },
  };
  </script>
