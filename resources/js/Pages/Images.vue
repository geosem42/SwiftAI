<template>
  <app-layout title="Dashboard">
    <template #header>
      <h2 class="h4 font-weight-bold">
        AI Images
      </h2>
    </template>

    <div class="toggle-conversations bg-primary">
      <button class="menu-btn text-warning" @click="toggleImageGenerator" ref="menuBtn2">
        <i class="bi bi-list"></i>
      </button>
    </div>

    <div class="images-wrapper position-relative">
      <div class="sidebar sidebar-2" :class="{ 'show-generator': showImageGenerator }">
        <div class="container mt-5">
          <div class="row mb-3">
            <div class="col-md-12">
              <textarea name="prompt" id="prompt" cols="5" rows="3" v-model="form.prompt"
                placeholder="What would you like to see?" class="form-control form-control-lg bg-primary text-white border-light"
                :class="{ 'is-invalid': errors.prompt }" @input="clearError('prompt')">
              </textarea>
              <div v-if="errors.prompt" class="invalid-feedback">
                {{ errors.prompt[0] }}
              </div>

            </div>
          </div>
          <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mt-3">
              <label for="artstyle" class="form-label text-white fs-xs">Art Style</label>
              <select name="artstyle" class="form-select form-select-lg bg-primary border-0 text-white"
                aria-label="Select style preset" id="artstyle" v-model="form.style">
                <option value="3d-model">3D Model</option>
                <option value="analog-film">Analog Film</option>
                <option value="anime">Anime</option>
                <option value="cinematic">Cinematic</option>
                <option value="comic-book">Comic Book</option>
                <option value="digital-art">Digital Art</option>
                <option value="enhance">Enhance</option>
                <option value="fantasy-art">Fantasy Art</option>
                <option value="isometric">Isometric</option>
                <option value="line-art">Line Art</option>
                <option value="low-poly">Low Poly</option>
                <option value="modeling-compound">Modeling Compound</option>
                <option value="neon-punk">Neon Punk</option>
                <option value="origami">Origami</option>
                <option value="photographic">Photographic</option>
                <option value="pixel-art">Pixel Art</option>
                <option value="tile-texture">Tile Texture</option>
              </select>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 mt-5">
              <label for="resolutionRange" class="form-label text-white fs-xs">Resolution</label>
              <span class="badge bg-primary float-end">{{ currentResolution.width }} x {{
                currentResolution.height
              }}</span>
              <input name="resolution" id="resolutionRange" type="range" class="form-range" :min="1"
                :max="resolution.length" :value="currentRes" @input="changeRes($event)">
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 mt-5">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="upscale" id="upscale" v-model="form.upscaleImage">
                <label class="form-check-label text-white" for="upscale">
                  Upscale Image?
                </label>
              </div>
            </div>
          </div>
          <div class="d-grid mt-4">
            <button v-if="!isLoading" class="btn btn-shade btn-lg text-secondary" @click.prevent="createImage">Generate
              Image</button>
            <button v-if="isLoading" class="btn btn-shade btn-lg d-flex justify-content-center align-items-center" type="button" disabled>
              <span class="spinner-border spinner-border-md" role="status" aria-hidden="true"></span>
              <span class="visually-hidden">Loading...</span>
            </button>
            <span class="disclaimer text-center">
              Please refer to Stability AI's <a href="https://openai.com/policies/terms-of-use" class="link-secondary"
                target="_blank">terms of use</a>.
            </span>
          </div>
        </div>
      </div>
      <div class="content">
        <div class="container" :class="{ 'slide-down': isPushed }">
          <transition-group name="slide-down" tag="div">
            <div class="image-container" v-for="image in imagesList" :key="image.original">
              <p class="image-prompt text-white fw-bold fs-6 mb-2">{{ image.prompt }}</p>
              <div class="wrapper-img">
                <img :src="'storage/' + image.original" class="img-thumbnail image child" alt="Generated Image" />
                <div class="middle d-flex flex-row justify-content-center">
                  <a :href="'storage/' + image.original" class="btn btn-secondary me-1" data-bs-toggle="tooltip"
                    data-bs-title="Download" download>
                    <i class="bi bi-cloud-download"></i>
                  </a>
                  <a v-if="image.upscaled" :href="'storage/' + image.upscaled" class="btn btn-secondary" data-bs-toggle="tooltip"
                    data-bs-title="Upscale" download>
                    <i class="bi bi-arrows-angle-expand"></i>
                  </a>
                </div>
              </div>
            </div>
          </transition-group>

          <button v-if="hasMoreImages && !isLoadingMore" @click="loadMore" :disabled="isLoadingMore"
            class="btn btn-lg btn-secondary mt-3 mb-3">
            <span v-if="isLoadingMore" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span v-if="isLoadingMore" class="visually-hidden">Loading...</span>
            <span v-else>Load More</span>
          </button>


          <!-- Scroll To Top Button -->
          <button v-show="showScrollToTop" @click="scrollToTop" class="scroll-to-top">
            <i class="bi bi-arrow-up"></i>
          </button>
        </div>

      </div>
    </div>
  </app-layout>
</template>

<script>
import { defineComponent } from "vue"
import { useForm } from '@inertiajs/vue3'
import JetApplicationLogo from '@/Jetstream/ApplicationLogo.vue'
import AppLayout from "@/Layouts/AppLayout.vue"
import Pagination from '@/Components/Pagination.vue'
import { Tooltip } from 'bootstrap'
import SimpleBar from 'simplebar';
import 'simplebar/dist/simplebar.min.css';
import { handleError, normalizeErrors } from '@/utils/errorHandler.js';
import { fetchCsrfToken, generateImage, getInitialImages, getMoreImages } from '@/utils/apiService.js';

export default defineComponent({
  props: {

  },
  components: {
    AppLayout,
    JetApplicationLogo,
    Pagination
  },
  data() {
    return {
      resolution: [
        { id: 1, width: 896, height: 512 },
        { id: 2, width: 512, height: 512 },
        { id: 3, width: 512, height: 896 },
      ],
      currentRes: 2,
      form: useForm({
        prompt: '',
        style: '3d-model',
        width: '',
        height: '',
        upscaleImage: false
      }),
      generatedImageUrl: null,
      imagesList: [],
      isLoading: false,
      hasMoreImages: true,
      isInitialRender: true,
      isPushed: false,
      nextPageUrl: null,
      isLoadingMore: false,
      showScrollToTop: true,
      simpleBarInstance: null,
      showImageGenerator: false,
      errors: {}
    }
  },
  computed: {
    currentResolution() {
      return this.resolution.find(r => r.id == this.currentRes)
    },
    noMoreImages() {
      return this.imagesList.length > 0 && !this.nextPageUrl;
    }
  },
  async mounted() {
    new Tooltip(document.body, {
      selector: "[data-bs-toggle='tooltip']",
    });

    // Initialize SimpleBar
    const contentContainer = document.querySelector('.content');
    if (contentContainer) {
      this.simpleBarInstance = new SimpleBar(contentContainer);
      this.simpleBarInstance.getScrollElement().addEventListener('scroll', this.toggleScrollToTopButton);
    }

    // Fetch the initial set of images when the component is mounted
    this.loadMore();
  },
  methods: {
    async createImage() {
      try {

        this.isLoading = true; // Start loading

        const csrfToken = await fetchCsrfToken();
        const prompt = this.form.prompt;
        const style = this.form.style;
        const width = this.form.width;
        const height = this.form.height;
        const upscaleImage = this.form.upscaleImage;
        const response = await generateImage(csrfToken, prompt, style, width, height, upscaleImage);

        // Append new image to imagesList
        this.imagesList.unshift({
          prompt: response.prompt,
          original: response.original,
          upscaled: response.upscaled
        });

        this.isLoading = false; // Stop loading
        this.errors = {};

      } catch (error) {
        if (error.source === 'frontend') {
          // Handle frontend validation errors
          this.errors = error.errors;
        } else {
          // Handle backend validation errors
          //console.log(error)
          this.errors = normalizeErrors(error || { message: ['An unexpected error occurred.'] });
        }
      } finally {
        this.isLoading = false
      }
    },
    changeRes(event) {
      this.currentRes = parseInt(event.target.value, 10);
    },
    toggleImageGenerator() {
			this.showImageGenerator = !this.showImageGenerator;
		},
    applyAnimation() {
      const firstImage = document.querySelector(".image-container");
      if (!firstImage) return;

      firstImage.style.opacity = 0;
      firstImage.style.transform = "translateY(-100%)";

      setTimeout(() => {
        firstImage.style.transition = "all 0.4s ease";
        firstImage.style.opacity = 1;
        firstImage.style.transform = "translateY(0)";
      }, 100);
    },
    async loadMore() {
      if (this.hasMoreImages === false) {
        return;
      }

      // Set isLoading to true when fetching starts
      this.isLoading = true;
      this.isLoadingMore = true;

      try {
        const url = (this.imagesList.length === 0) ? '/get-images' : this.nextPageUrl;

        if (url) {
          const response = await getMoreImages(url);

          if (Array.isArray(response)) {
            this.imagesList = [...(this.imagesList || []), ...response];
            this.hasMoreImages = false;
          } else if (response && response.data) {
            this.imagesList = [...(this.imagesList || []), ...response.data];
            if (response.data.length > 0) {
              this.nextPageUrl = response.next_page_url;
              this.hasMoreImages = !!this.nextPageUrl;
            } else {
              this.hasMoreImages = false;
            }
          } else {
            console.error("Unexpected response structure:", response);
          }
        } else {
          console.error("No URL available for fetching more images.");
        }

      } catch (error) {
        handleError(error);
      } finally {
        // Set isLoading back to false once fetching is complete
        this.isLoading = false;
        this.isLoadingMore = false;
      }
    },
    scrollToTop() {
      const contentContainer = document.querySelector('.content');
      if (contentContainer) {
        const scrollContent = contentContainer.querySelector('.simplebar-content-wrapper');
        if (scrollContent) {
          this.animateScroll(scrollContent);
        } else {
          console.log('Scroll content not found');
        }
      } else {
        console.log('Content container not found');
      }
    },
    animateScroll(element) {
      let start = element.scrollTop;
      let change = 0 - start;
      let duration = 500;
      let startTimestamp;

      function step(timestamp) {
        if (!startTimestamp) startTimestamp = timestamp;
        let progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.scrollTop = start + progress * change;
        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      }

      window.requestAnimationFrame(step);
    },
    toggleScrollToTopButton() {
      if (this.simpleBarInstance) {
        const scrollTopPosition = this.simpleBarInstance.getScrollElement().scrollTop;
        const scrollTopButton = document.querySelector('.scroll-to-top');
        if (scrollTopButton) {
          // Show the button if the scroll position is greater than 200px, hide it otherwise
          if (scrollTopPosition > 500) {
            scrollTopButton.style.display = 'flex';
          } else {
            scrollTopButton.style.display = 'none';
          }
        }
      }
    },
    clearError(field) {
      if (this.errors[field]) {
        this.errors[field] = ''; // Clear the error message
      }
    },
  },
  watch: {
    currentResolution: {
      immediate: true,
      handler(resolution) {
        this.form.width = resolution.width
        this.form.height = resolution.height
      },
    },
    imagesList(newVal, oldVal) {
      if (this.isInitialRender) {
        this.isInitialRender = false;
        return;
      }
      if (newVal.length > oldVal.length) {
        this.applyAnimation();
      }
    },
  },
});
</script>