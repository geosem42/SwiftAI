<template>
  <app-layout title="Text To Speech">
    <main class="h-100">
      <div v-if="areSelectFieldsLoading" class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div v-if="!areSelectFieldsLoading" class="container-fluid px-4">
        <h1 class="mt-4">Text To Speech</h1>
        <div class="row">
          <div class="col-12">
            <form class="row g-3">
              <div class="col-md-6">
                <label for="language" class="form-label">Language</label>
                <select class="form-select" :class="{'is-invalid': errors.language}" aria-label="Default select example" id="language" v-model="selectedLanguage"
                @change="onLanguageChange" @input="clearError('language')" name="language">
                  <option disabled value="">Please select a language</option>
                  <option v-for="language in languages" :value="language.code" :key="language.code">{{ language.name }}
                  </option>
                </select>
                <div class="invalid-feedback" v-if="errors.language">
                  {{ errors.language[0] }}
                </div>
              </div>
              <div class="col-md-6">
                <label for="voice" class="form-label">Voice</label>
                <select class="form-select" :class="{'is-invalid': errors.voice}" aria-label="Default select example" id="voice" v-model="selectedVoice"
                  :disabled="!selectedLanguage" @input="clearError('voice')" name="voice">
                  <option disabled value="">Please select a voice</option>
                  <option v-for="voice in filteredVoices" :value="voice.ShortName" :key="voice.ShortName">{{
                    voice.DisplayName }}</option>
                </select>
                <div class="invalid-feedback" v-if="errors.voice">
                  {{ errors.voice[0] }}
                </div>
              </div>
              <div class="col-12">
                <label for="message" class="form-label">Message</label>
                <textarea name="message" id="message" cols="30" rows="10" class="form-control" :class="{'is-invalid': errors.message}" v-model="message" @input="clearError('message')"></textarea>
                <div class="invalid-feedback" v-if="errors.message">
                  {{ errors.message[0] }}
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg" @click.prevent="narrate" :disabled="isLoading">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  <span v-if="!isLoading">Narrate</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="row mt-5">
          <div class="col-12">
            <div class="list-group mb-5">
              <a v-for="(audioFile, index) in audioFiles" :key="audioFile.id" class="list-group-item list-group-item-action" aria-current="true">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1"><strong>{{ formatName(audioFile.voice) }}</strong> ({{ audioFile.language }})</h5>
                  <small>{{ formatCreatedAt(audioFile.created_at) }}</small>
                </div>
                <p class="mb-1">
                  {{ audioFile.message }}
                </p>
                <small>
                  <audio controls :src="'/storage/audio/' + audioFile.filename">
                    Your browser does not support the audio element.
                  </audio>
                </small>
              </a>
            </div>
            <div id="loadmore" class="text-center">
              <button v-if="!isFetchingMore && currentPage < lastPage" class="btn btn-lg btn-secondary mb-5" @click="loadMore">Load More</button>
              <div v-if="isFetchingMore" class="spinner-border text-primary mt-4" role="status">
                <span class="visually-hidden">Loading...</span>
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
import moment from 'moment';
import { fetchCsrfToken, fetchVoices, generateAudio, fetchAudioFiles } from '@/utils/apiService.js';
import { handleError, normalizeErrors } from '@/utils/errorHandler.js';

export default defineComponent({
  components: {
    AppLayout
  },
  data() {
    return {
      voices: [],
      selectedLanguage: '',
      selectedVoice: '',
      message: null,
      isLoading: false,
      areSelectFieldsLoading: true,
      errors: {},
      audioFiles: [],
      currentPage: 1,
      lastPage: null,
      isFetchingMore: false
    };
  },
  methods: {
    async fetchVoices() {

      this.areSelectFieldsLoading = true;
      
      try {

        const csrfToken = await fetchCsrfToken();
        const response = await fetchVoices(csrfToken);
        this.voices = response;

        // Set default selected voice after fetching the voices
        this.setDefaultVoice();

      } catch (error) {

        console.error('Error fetching voices:', error);

      } finally {

        this.areSelectFieldsLoading = false;

      }
    },
    async narrate() {

      this.isLoading = true; // Start loading

      try {

        const csrfToken = await fetchCsrfToken();
        const response = await generateAudio(csrfToken, this.selectedLanguage, this.selectedVoice, this.message);

        // Handle response here
        // Assuming that the response contains the newly generated audio file object
        // Unshift it to the beginning of the audioFiles array
        if (response && response.file) {
            this.audioFiles.unshift({
                filename: response.file,
                voice: this.selectedVoice,
                language: this.selectedLanguage,
                message: this.message
            });
        }

        this.isLoading = false; // Stop loading
        //this.errors = {};

      } catch (error) {
        this.isLoading = false; // Stop loading in case of error

        if (error.source === 'frontend') {
          // Handle frontend validation errors
          this.errors = error.errors;
        } else {
          // Handle backend validation errors
          //console.log(error)
          this.errors = normalizeErrors(error || { message: ['An unexpected error occurred.'] });
        }
      } finally {
        this.isLoading = false; // Stop loading - This should hide the spinner
      }
    },
    async fetchAudioFiles(page = 1) {

      // If fetching more audio files (not the initial load), set isFetchingMore to true
      if (page > 1) {
        this.isFetchingMore = true;
      }

      try {
        const data = await fetchAudioFiles(page); // this is imported from apiService.js
        
        if (data && data.data) {
          this.audioFiles = [...this.audioFiles, ...data.data];
          this.currentPage = data.current_page;
          this.lastPage = data.last_page;
        }
      } catch (error) {
        console.error('Error fetching audio files:', error);
      } finally {
        if (page > 1) {
          this.isFetchingMore = false;
        }
      }
    },

    loadMore() {
      this.fetchAudioFiles(this.currentPage + 1);
    },
    setDefaultVoice() {
      const filtered = this.filteredVoices;
      if (filtered.length > 0) {
        const defaultVoice = filtered.find(voice => voice.ShortName === 'en-US-DavisNeural');
        this.selectedVoice = defaultVoice ? defaultVoice.ShortName : filtered[0].ShortName;
      } else {
        this.selectedVoice = null;
      }
    },
    onLanguageChange() {
      this.setDefaultVoice();
    },
    formatName(fullName) {
      const parts = fullName.split('-');
      let displayName = parts.length > 2 ? parts[2] : fullName;

      // Remove 'Neural' suffix if it exists
      if (displayName.endsWith('Neural')) {
          displayName = displayName.slice(0, -'Neural'.length);
      }
      
      return displayName;
    },
    formatCreatedAt(date) {
			return moment(date).fromNow();
		},
    clearError(field) {
      if (this.errors[field]) {
        this.errors[field] = ''; // Clear the error message
      }
    },
  },
  mounted() {
    this.fetchVoices();
    this.fetchAudioFiles();
  },
  computed: {
    languages() {
      const languageSet = new Set();
      if (Array.isArray(this.voices)) {
        this.voices.forEach(voice => {
          languageSet.add(JSON.stringify({ name: voice.LocaleName, code: voice.Locale }));
        });
      }
      return Array.from(languageSet).map(JSON.parse);
    },
    filteredVoices() {
      return Array.isArray(this.voices) ? this.voices.filter(voice => voice.Locale === this.selectedLanguage) : [];
    },
    showLoadMoreButton() {
        return this.audioFiles.length % (10 + 1) === 0 && this.audioFiles.length > 0;
    },
  },
  watch: {
    filteredVoices(newFilteredVoices) {
      // When filteredVoices changes, update selectedVoice to the first voice in the filtered list
      this.selectedVoice = newFilteredVoices.length > 0 ? newFilteredVoices[0].ShortName : null;
    }
  },
});
</script>