<template>
    <div class="container mt-4">
      <h3>Gửi thông báo</h3>
      <form @submit.prevent="sendNotification" class="mt-3">
        <div class="mb-3">
          <label class="form-label">Tiêu đề</label>
          <input type="text" class="form-control" v-model="title" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Nội dung</label>
          <textarea class="form-control" rows="4" v-model="message" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Gửi</button>
      </form>
    </div>
  </template>

  <script>
  // Gửi thông báo hệ thống
  export default {
    name: "SendNotification",
    data() {
      return {
        title: "",
        message: "",
      };
    },
    methods: {
      sendNotification() {
        alert(`Đã gửi thông báo: ${this.title}`);
        this.title = "";
        this.message = "";
      },
    },
  };
  </script>
