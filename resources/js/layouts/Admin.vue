<template>
    <div id="wrapper">
      <Sidebar />

      <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
          <Header />
          <router-view /> <!-- Nội dung động -->
        </div>

        <Footer />
      </div>

      <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
      </a>
    </div>
  </template>

  <script>
  import Header from '../components/Header.vue'
  import Sidebar from '../components/Sidebar.vue'
  import Footer from '../components/Footer.vue'

  export default {
    name: 'AdminLayout',
    components: {
      Header,
      Sidebar,
      Footer,
    },
  }
  </script>

  <style scoped>
  input.form-control:focus {
    outline: none;
    box-shadow: none;
    border-color: none;
  }
  </style>
