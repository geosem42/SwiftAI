<template>
  <div id="page">

    <Head :title="title" />

    <jet-banner />

    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-white p-0">
      <!-- Navbar Brand-->
      <a class="navbar-brand ps-3 text-warning bg-primary h-100 fw-bold d-flex align-items-center" href="/">Swift AI</a>
      <!-- Sidebar Toggle-->
      <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-shade" id="sidebarToggle" href="#">
        <i class="bi bi-list fs-3"></i>
      </button>

      <!-- Navbar-->
      <ul class="navbar-nav ms-auto me-3 me-lg-4" v-if="$page.props.user">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle user-profile" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <img v-if="$page.props.jetstream.managesProfilePhotos" class="rounded-circle" width="32" height="32"
              :src="$page.props.user.profile_photo_url" :alt="$page.props.user.name" />
            <span v-else>
              <i class="bi bi-person-circle fs-3"></i>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><Link :href="route('profile.show')" class="dropdown-item">Profile</Link></li>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <li>
              <form @submit.prevent="logout">
                <jet-dropdown-link as="button" class="dropdown-item">
                  Log out
                </jet-dropdown-link>
              </form>
            </li>
          </ul>
        </li>
      </ul>

      <div class="text-end ms-auto me-3" v-if="!$page.props.user">
        <a :href="route('login')" class="btn btn-primary me-2">Login</a>
        <a :href="route('register')" class="btn btn-secondary">Register</a>
      </div>
    </nav>

    <div id="layoutSidenav">
      <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
          <div class="sb-sidenav-menu">
            <div class="nav">
              <div class="sb-sidenav-menu-heading">Core</div>
              <Link class="nav-link" :href="route('home')" :active="route().current('home')" :class="{ 'active': $page.url === '/' }" aria-current="page">
                <div class="sb-nav-link-icon">
                  <i class="bi bi-grid fs-6"></i>
                </div>
                Dashboard
              </Link>
              <Link class="nav-link" :href="route('chat')" :active="route('chat')" :class="{ 'active': $page.url === '/chat' }">
                <div class="sb-nav-link-icon">
                  <i class="bi bi-chat-square-dots fs-6"></i>
                </div>
                AI Chat
              </Link>
              <Link class="nav-link" :href="route('images')" :active="route('images')" :class="{ 'active': $page.url.startsWith('/images') }">
                <div class="sb-nav-link-icon">
                  <i class="bi bi-images fs-6"></i>
                </div>
                AI Images
              </Link>
              <Link class="nav-link" :href="route('document')" :active="route('document')" :class="{ 'active': $page.url === '/document' }">
                <div class="sb-nav-link-icon">
                  <i class="bi bi-file-text fs-6"></i>
                </div>
                AI Documents
              </Link>
              <Link class="nav-link" :href="route('texttospeech')" :active="route('texttospeech')" :class="{ 'active': $page.url === '/texttospeech' }">
                <div class="sb-nav-link-icon">
                  <i class="bi bi-mic fs-6"></i>
                </div>
                Text To Speech
              </Link>
              <Link class="nav-link" :href="route('speechtotext')" :active="route('speechtotext')" :class="{ 'active': $page.url === '/speechtotext' }">
                <div class="sb-nav-link-icon">
                  <i class="bi bi-volume-up fs-6"></i>
                </div>
                Speech To Text
              </Link>
            </div>
          </div>
        </nav>
      </div>
      <div id="layoutSidenav_content">
        <main class="h-100">
          <slot></slot>
        </main>
        <!-- <footer class="py-4 bg-light mt-auto">
          <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
              <div class="text-muted">Copyright &copy; Your Website 2023</div>
              <div>
                <a href="#">Privacy Policy</a>
                &middot;
                <a href="#">Terms &amp; Conditions</a>
              </div>
            </div>
          </div>
        </footer> -->
      </div>
    </div>

  </div>
</template>

<script>
import JetApplicationLogo from '@/Jetstream/ApplicationLogo.vue'
import JetBanner from '@/Jetstream/Banner.vue'
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue'
import JetDropdown from '@/Jetstream/Dropdown.vue'
import JetDropdownLink from '@/Jetstream/DropdownLink.vue'
import JetNavLink from '@/Jetstream/NavLink.vue'
import { Head, Link } from '@inertiajs/vue3'

export default {
  props: {
    title: String,
  },

  components: {
    Head,
    JetApplicationLogo,
    JetBanner,
    JetApplicationMark,
    JetDropdown,
    JetDropdownLink,
    JetNavLink,
    Link,
  },

  data() {
    return {
      showingNavigationDropdown: false,
    }
  },

  methods: {
    switchToTeam(team) {
      this.$inertia.put(route('current-team.update'), {
        'team_id': team.id
      }, {
        preserveState: false
      })
    },

    logout() {
      this.$inertia.post(route('logout'));
    },
  },

  mounted() {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
      if (sidebarToggle) {
        // Persist sidebar toggle between refreshes
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.toggle('sb-sidenav-toggled');
        }
        sidebarToggle.addEventListener('click', event => {
          console.log(event);
          document.body.classList.toggle('sb-sidenav-toggled');
          localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
      }
  },

  computed: {
    path() {
      return window.location.pathname
    }
  },
}
</script>