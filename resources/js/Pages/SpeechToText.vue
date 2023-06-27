<template>
  <app-layout title="Speech To Text">
    <main class="h-100 position-relative">

      <div v-if="state.isLoading" class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-if="!state.isLoading" class="container-fluid px-4">
        <h1 class="mt-4">Speech To Text</h1>

        <div class="mb-3">

          <div id="upload-form" class="bg-primary p-4 text-white rounded">
            <label for="formFile" class="form-label">Upload Audio File</label>
            <input class="form-control" type="file" id="formFile" ref="fileInput" name="file" accept=".mp3"
              @change="handleFileUpload" @input="clearError('file')" :class="{ 'is-invalid': errors.file }">
            <div class="invalid-feedback" v-if="errors.file">{{ errors.file[0] }}</div>

            <button type="button" class="btn btn-secondary mt-2" @click.prevent="transcribeAudio"
              :disabled="isTranscribing">
              <span v-if="isTranscribing" class="spinner-border spinner-border-sm" role="status"
                aria-hidden="true"></span>
              <span v-if="!isTranscribing">Upload</span>
            </button>
          </div>

          <!-- Table for uploaded files -->
          <div v-if="transcriptions.length > 0">
            <div class="card bg-light mt-5" v-for="transcription in transcriptions" :key="transcription.id">
              <h5 class="card-header">{{ transcription.file }}</h5>
              <div class="card-body">
                <h5 class="card-title">
                  <audio controls :src="'/storage/transcriptions/' + transcription.unique_name">
                    Your browser does not support the audio element.
                  </audio>
                </h5>
                <p class="card-text">{{ transcription.text }}</p>
              </div>
            </div>
          </div>
          <!-- Message when there are no transcriptions -->
          <div v-else class="alert alert-light mt-3">
            You don't have any transcriptions yet!
          </div>

          <div id="loadmore" class="text-center">
            <button v-if="!isFetchingMore && currentPage < lastPage" class="btn btn-lg btn-secondary mt-5 mb-5"
              @click="loadMore">Load More</button>
            <div v-if="isFetchingMore" class="d-flex justify-content-center mt-4">
              <div class="spinner-border text-primary" style="width: 2rem; height: 2rem;" role="status">
                <span class="visually-hidden">Loading more...</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </app-layout>
</template>

<script>
import { defineComponent } from "vue"
import AppLayout from "@/Layouts/AppLayout.vue"
import { fetchCsrfToken, uploadAudioForTranscription, fetchTranscriptions as fetchTranscriptionsFromApi } from '@/utils/apiService.js';
import { handleError, normalizeErrors } from '@/utils/errorHandler.js';

export default defineComponent({
  components: {
    AppLayout
  },
  data() {
    return {
      file: null,
      transcriptions: [],
      errors: {},
      isTranscribing: false,
      state: {
        isLoading: true,
      },
      currentPage: 1,
      lastPage: null,
      isFetchingMore: false
    };
  },
  async mounted() {
    try {
      console.log("Before fetching transcriptions, isLoading:", this.state.isLoading); // log statement
      this.state.isLoading = true
      console.log("Fetching transcriptions, isLoading:", this.state.isLoading); // log statement
      this.fetchTranscriptions(this.currentPage)

    } catch (error) {
      handleError(error);
      this.errors = error.errors || { message: 'An unexpected error occurred while fetching transcriptions.' };
    } finally {
      //this.state.isLoading = false
    }
  },
  methods: {
    handleFileUpload() {
      const fileInput = this.$refs.fileInput;
      this.file = fileInput.files[0];
    },
    loadMore() {
      this.fetchTranscriptions(this.currentPage + 1);
    },

    async transcribeAudio() {

      this.isTranscribing = true;

      try {
        const csrfToken = await fetchCsrfToken();
        const data = await uploadAudioForTranscription(this.file, csrfToken);

        // Success
        this.transcriptions.unshift({
          file: this.file.name,
          unique_name: data.unique_name,
          text: data.text
        });

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
        this.isTranscribing = false;
        this.file = null;
        this.$refs.fileInput.value = null;
      }
    },
    async fetchTranscriptions(page) {
      // Only set state.isLoading to true on initial page load
      if (page === 1) {
        this.state.isLoading = true;
      } else {
        this.isFetchingMore = true; // Set this to true only when fetching more items
      }

      try {
        const csrfToken = await fetchCsrfToken();
        const data = await fetchTranscriptionsFromApi(csrfToken, page);

        if (data && data.data) {
          this.transcriptions = [...this.transcriptions, ...data.data];
          this.currentPage = data.current_page;
          this.lastPage = data.last_page;
        }
      } catch (error) {
        console.error('Error fetching transcriptions:', error);
      } finally {
        // Only set state.isLoading to false on initial page load
        if (page === 1) {
          this.state.isLoading = false;
        } else {
          this.isFetchingMore = false;
        }
      }
    },
    clearError(field) {
      if (this.errors[field]) {
        this.errors[field] = ''; // Clear the error message
      }
    },
  }
});
</script>