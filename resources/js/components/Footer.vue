<template>
    <footer class="sticky-footer bg-white shadow-sm mt-auto py-3">
      <div class="container my-auto text-center">
        <span class="text-muted">© {{ currentYear }} Studying For Exams. All rights reserved.</span>
      </div>
    </footer>
  </template>

  <script>
  export default {
    name: "Footer",
    data() {
      return {
        currentYear: new Date().getFullYear()
      };
    }
  };
  </script>

  <style scoped>
  footer {
    border-top: 1px solid #e3e6f0;
  }
  </style>
