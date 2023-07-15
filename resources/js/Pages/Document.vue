<template>
  <app-layout title="Documents">
    <main class="h-100">
      <div v-if="isPageLoading" class="loading-spinner-container d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div v-else class="container-fluid px-4">
        <h1 class="mt-4">Documents</h1>

        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ successMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              <!-- <div v-if="errors.file" class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ errors.file }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div> -->
            </div>
            <div class="bg-primary text-white rounded p-4 mb-3">
              <label for="formFile" class="form-label">Upload PDF Document</label>

              <input class="form-control" type="file" id="formFile" ref="fileInput" name="file" accept=".pdf"
                @input="clearError('file')" :class="{ 'is-invalid': errors.file }">

              <div class="invalid-feedback" v-if="errors.file">{{ errors.file[0] }}</div>

              <button type="button" class="btn btn-secondary mt-2" @click.prevent="uploadDocument" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span v-if="!isLoading">Upload</span>
              </button>

            </div>
          </div>
        </div>

        <div class="row" v-if="documents.length > 0">
          <div class="col-12">
            <table v-if="!isTableLoading" class="table">
              <thead>
                <tr>
                  <th scope="col-11">File</th>
                  <th scope="col-1"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="document in documents" :key="document ? document.id : ''" v-if="documents">
                  <td class="ps-0">
                    <button type="button" class="btn btn-link" @click.prevent="openQAModal(document)">{{
                      document.file_name }}</button>
                  </td>
                  <td class="table-actions">
                    <button @click="deleteDocument(document.id)" class="btn btn-danger">
                      <i class="bi bi-archive"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>

        <nav v-if="pagination.total > 10" aria-label="Page navigation" class="shadow-none d-flex justify-content-evenly ">
          <ul class="pagination">
            <li class="page-item" :class="{ 'disabled': pagination.current_page === 1 }">
              <a class="page-link bg-transparent text-white" href="#"
                @click.prevent="fetchDocuments(pagination.current_page - 1)">Previous</a>
            </li>
            <li class="page-item" :class="{ 'active': page === pagination.current_page }"
              v-for="page in pagination.last_page" :key="page">
              <a class="page-link bg-transparent text-white" href="#" @click.prevent="fetchDocuments(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ 'disabled': pagination.current_page === pagination.last_page }">
              <a class="page-link bg-transparent text-white" href="#"
                @click.prevent="fetchDocuments(pagination.current_page + 1)">Next</a>
            </li>
          </ul>
        </nav>

      </div>

      <!-- Q&A Modal -->
      <div class="modal fade" tabindex="-1" ref="qaModal" aria-labelledby="qaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="qaModalLabel">Q&A Session</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="card h-100" id="chat2">
                <div class="card-header d-flex justify-content-between align-items-center p-3">
                  <h5 class="mb-0" v-if="selectedDocument">{{ selectedDocument.file_name }}</h5>
                </div>
                <div class="card-body" ref="cardBody" style="position: relative; height: 400px">

                  <div class="d-flex flex-row justify-content-start mb-4" v-for="chatMessage in chatMessages"
                    :key="chatMessage.message">
                    <div v-if="chatMessage.isUser">
                      <!-- Message from User -->
                      <p class="small p-2 ms-3 mb-1 rounded-3 bg-primary text-white">{{ chatMessage.message }}</p>
                    </div>
                    <div v-else>
                      <!-- Message from AI -->
                      <p class="small p-2 ms-3 mb-1 rounded-3" style="background-color: #f5f6f7;">{{ chatMessage.message
                      }}</p>
                    </div>
                  </div>

                </div>
                <div class="card-footer text-muted d-flex justify-content-start align-items-center p-3">

                  <input type="text" class="form-control form-control-lg" placeholder="Ask a question..."
                    v-model="userMessage" @keyup.enter="sendMessage" :disabled="isSending" />
                  <div class="input-group-append" v-if="isSending">
                    <div class="input-group-text bg-transparent">
                      <div class="spinner-border spinner-border" role="status">
                        <span class="sr-only"></span>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </app-layout>
</template>

<script>
import { defineComponent, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import { Modal } from 'bootstrap';
import SimpleBar from 'simplebar';
import 'simplebar/dist/simplebar.min.css';
import { fetchCsrfToken, fetchDocuments, deleteDocument, searchDocument, uploadDocument as apiUploadDocument, fetchChatHistory as apifetchChatHistory } from "@/utils/apiService.js";
import { handleError, normalizeErrors } from '@/utils/errorHandler.js';

export default defineComponent({
  components: {
    AppLayout,
    Link
  },
  data() {
    return {
      file: null,
      documentUploaded: false,
      question: "",
      answer: null,
      errors: {},
      documents: [],
      isLoading: false,
      isPageLoading: true,
      isTableLoading: false,
      isSending: false,
      successMessage: '',
      activeDocument: null,
      bootstrapModalInstance: null,
      selectedDocument: null,
      qaModal: null,
      userMessage: '',
      chatMessages: [],
      simpleBarInstance: null,
      pagination: {
        current_page: 1,
        last_page: 1,
      },
    };
  },
  mounted() {
    this.fetchDocuments();
    this.qaModal = new Modal(this.$refs.qaModal);

    this.$refs.qaModal.addEventListener('shown.bs.modal', this.scrollToBottom);

    const contentContainer = this.$refs.cardBody;
    if (contentContainer) {
      new SimpleBar(contentContainer);
    } else {
      console.log('Content container not found');
    }
  },
  methods: {
    async uploadDocument() {
      this.isLoading = true;
      const csrfToken = await fetchCsrfToken();
      const fileInput = this.$refs.fileInput;
      const file = fileInput.files[0];

      try {
        const response = await apiUploadDocument(file, csrfToken);
        //console.log(response);

        this.fetchDocuments(1);

        // Extract the document data from the response
        const uploadedDocument = response.document;

        // Unshift the document data into the documents array
        this.documents.unshift(uploadedDocument);

        this.successMessage = 'Document has been uploaded successfully.';

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
    async fetchDocuments(page = 1) {
      this.isTableLoading = true;
      try {
        const csrfToken = await fetchCsrfToken();
        const response = await fetchDocuments(csrfToken, page);

        // If the current page is empty and is not the first page,
        // then fetch the previous page.
        if (response.data.length === 0 && page > 1) {
          this.fetchDocuments(page - 1);
          return;
        }


        this.documents = response.data;
        this.pagination = response;
      } catch (error) {
        console.error('Error fetching documents:', error);
      } finally {
        this.isPageLoading = false;
        this.isTableLoading = false;
      }
    },
    async deleteDocument(documentId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const csrfToken = await fetchCsrfToken();
            const response = await deleteDocument(documentId, csrfToken);
            //console.log(response);

            // Refetch documents for the current page
            this.fetchDocuments(this.pagination.current_page);

            // Find the index by documentId
            const index = this.documents.findIndex(doc => doc.id === documentId);

            // If found, remove the document from the array
            if (index > -1) {
              this.documents.splice(index, 1);
            }

            Swal.fire(
              'Deleted!',
              'Your document has been deleted.',
              'success'
            );
          } catch (error) {
            console.error('Error deleting document:', error);

            Swal.fire(
              'Error!',
              'There was an error deleting the document.',
              'error'
            );
          }
        }
      });
    },
    async sendMessage() {
      if (this.userMessage.trim() === '') return; // Don't send empty messages

      this.isSending = true;

      // Add user message to chat
      this.chatMessages.push({ message: this.userMessage, isUser: true });

      // Clear the user message input
      const message = this.userMessage;
      this.userMessage = '';

      this.scrollToBottom();

      // Set loading status
      this.isLoading = true;

      try {
        const csrfToken = await fetchCsrfToken();
        const response = await searchDocument(message, this.selectedDocument.id, csrfToken);

        // Log the response
        //console.log(response);

        // Add AI response to chat
        this.chatMessages.push({ message: response.answer, isUser: false });

        this.scrollToBottom();

      } catch (error) {
        console.error('Error searching document:', error);
      } finally {
        // Reset loading status
        this.isLoading = false;
        this.isSending = false;
      }
    },
    async fetchChatHistory(documentId) {
      try {
        const csrfToken = await fetchCsrfToken();
        const chatHistory = await apifetchChatHistory(documentId, csrfToken);

        // Return the chat history
        return chatHistory;
      } catch (error) {
        console.error('Error fetching chat history:', error);
      }
    },
    async openQAModal(document) {
      // Fetch chat history for the selected document
      const chatHistory = await this.fetchChatHistory(document.id);

      // Update chatMessages with the fetched history
      this.chatMessages = chatHistory.map(item => ({
        message: item.message,
        isUser: item.isUser
      }));

      // Set the selected document
      this.selectedDocument = document;

      // Show the modal
      this.qaModal.show();
    },
    clearError(field) {
      if (this.errors[field]) {
        this.errors[field] = ''; // Clear the error message
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const cardBody = this.$refs.cardBody;
        if (cardBody) {
          const simpleBar = SimpleBar.instances.get(cardBody);
          if (simpleBar) {
            const scrollElement = simpleBar.getScrollElement();
            scrollElement.scrollTop = scrollElement.scrollHeight;
          }
        }
      });
    },
  },
});
</script>