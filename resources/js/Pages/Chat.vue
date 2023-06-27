<template>
	<app-layout title="Chat">
		<div class="chat-layout d-flex h-100">
			<div class="d-flex flex-column align-items-stretch flex-shrink-0 conversations-list"
				:class="{ 'show-conversations': showConversationsList }" ref="conversationsList" data-simplebar>
				<a href="#" class="d-flex justify-content-between p-3 link-dark text-decoration-none conversations-header">
					<div class="list-label">
						<i class="bi bi-chat-left-text me-2"></i>
						<span class="fs-5 fw-semibold">Conversations</span>
					</div>
					<div class="new-conversation">
						<button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#new-conversation">New</button>
					</div>
				</a>
				<div class="list-group list-group-flush border-bottom scrollarea">
					<div v-if="conversations && conversations.length > 0">
						<a href="#" class="list-group-item list-group-item-action py-3 lh-tight"
							:class="{ active: conversation.id === currentConversationId }" v-for="conversation in conversations"
							:key="conversation.id" @click="selectConversation(conversation.id)">
							<div class="d-flex w-100 align-items-center justify-content-between">
								<strong class="mb-1 conversation-title">{{ conversation.title }}</strong>
								<small class="badge rounded-pill bg-warning text-dark">{{ conversation.message_count }}</small>
							</div>
						</a>
					</div>
					<div v-else class="list-group-item text-center py-3">
						Start a new conversation
					</div>
				</div>
			</div>

			<div v-if="isLoading" class="loading-messages d-flex justify-content-center align-items-center h-100">
				<div class="spinner-border text-warning" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>
			<div v-else-if="currentConversationId" class="chat-window">
				<div class="chat-container h-100 d-flex flex-column justify-content-between">

					<div class="toggle-conversations">
						<button class="menu-btn text-warning" @click="toggleConversationsList" ref="menuBtn">
							<i class="bi bi-list"></i> 
						</button>
					</div>
					
					<div class="chat-body" id="cbody" ref="chatBody" data-simplebar>
						<div v-for="(message, index) in messages" :key="index">
							<div class="message-wrapper" :class="{ user: message.isUser, assistant: !message.isUser }">
								<div class="list-group">
									<a class="list-group-item list-group-item-action bg-transparent text-white">
										<div class="d-flex w-100 justify-content-between">
											<h5 class="mb-1 text-warning" v-if="message.isUser" v-text="$page.props.user.name"></h5>
											<h5 class="mb-1 text-warning" v-else v-text="message.personality_name"></h5>
											<small>{{ formatCreatedAt(message.created_at) }}</small>
										</div>
										<span v-html="formatMessage(message.message)"></span>
									</a>
								</div>
							</div>
						</div>
					</div>

					<div class="chat-input">
						<div class="input-group">
							<div class="input-container position-relative d-flex flex-column align-items-center justify-content-evenly">

								<!-- Textarea -->
								<textarea ref="chatTextarea" id="chatbot-textarea" placeholder="Ask me anything..." v-model="form.message"
									@keydown.enter.exact.prevent="sendMessage(currentConversationId)" @input="clearError('message')"
									:key="currentConversationId" class="form-control" :class="{ 'is-invalid': errors.message }"
									:disabled="isWaitingForResponse"></textarea>

								<!-- Spinner -->
								<div v-if="isWaitingForResponse"
									class="spinner-border text-warning position-absolute" role="status">
									<span class="visually-hidden">Loading...</span>
								</div>

								<!-- Error message -->
								<div v-if="errors.message" class="invalid-feedback text-center">
									{{ errors.message[0] }}
								</div>
							</div>
						</div>
						<span class="textarea-disclaimer">
							Please refer to OpenAI's <a href="https://openai.com/policies/terms-of-use" class="link-secondary"
								target="_blank">terms of use</a>.
						</span>
					</div>
				</div>
			</div>
			<div v-else class="no-conversation-selected">
				<p class="text-muted">You don't have any conversations yet. Create a new conversation to start chatting!</p>
			</div>
		</div>

		<!-- New Conversation Modal -->
		<div class="modal fade" id="new-conversation" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
			aria-labelledby="newConversationLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header bg-primary text-white">
						<h5 class="modal-title" id="newConversationLabel">Create New Conversation</h5>
						<button type="button" class="btn-close bg-secondary" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="new-conversation-form">
							<div class="mb-3">
								<select v-model="newConv.personality_id" @change="clearError('personality')"
									class="form-select form-select-lg" :class="{ 'is-invalid': errors.personality_id }" id="personality"
									required>
									<option disabled value="">Please select a personality</option>
									<option v-for="personality in personalities" :value="personality.id" :key="personality.id">
										{{ personality.name }}
									</option>
								</select>
								<div v-if="errors.personality_id" class="invalid-feedback">{{ errors.personality_id[0] }}</div>
							</div>
							<div class="mb-3">
								<input type="text" class="form-control form-control-lg" :class="{ 'is-invalid': errors.title }" id="title"
									placeholder="Title" v-model="newConv.title" @input="clearError('title')" maxlength="35">
								<div class="invalid-feedback" v-if="errors.title">
									{{ errors.title[0] }}
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" @click.prevent="createNewConv">Create</button>
					</div>
				</div>
			</div>
		</div>

	</app-layout>
</template>

<script>
import { defineComponent, nextTick } from "vue"
import AppLayout from "@/Layouts/AppLayout.vue"
import { useForm } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it';
import moment from 'moment';
import SimpleBar from 'simplebar';
import 'simplebar/dist/simplebar.min.css';
import { handleError, normalizeErrors } from '@/utils/errorHandler.js';
import { fetchCsrfToken, createNewConversation, sendMessage, getMessages, getConversations, getPersonalities } from '@/utils/apiService.js';

export default defineComponent({
	props: {
		chats: Array
	},
	components: {
		AppLayout
	},
	data() {
		return {
			simplebarInstance: null,
			messages: [],
			conversations: [],
			personalities: [],
			currentConversationId: null,
			isLoading: false,
			showConversationsList: false,
			isWaitingForResponse: false,
			form: useForm({
				message: ''
			}),
			newConv: useForm({
				title: '',
				personality_id: ''
			}),
			errors: {},
		};
	},
	beforeMount() {
		//this.fetchChatMessages();
	},
	mounted() {
		if (this.currentConversationId) {
			this.$refs.chatTextarea.focus();
		}

		// Fetch conversations on mount
		this.fetchConversations();
		this.watchCurrentConversationId();

		// Close the conversations list when clicking outside of it
		document.addEventListener("click", this.handleOutsideClick);

		// Fetch personalities on mount
		this.fetchPersonalities();
	},
	beforeDestroy() {
		// Clean up the event listener when the component is destroyed
		document.removeEventListener("click", this.handleOutsideClick);
	},
	created() {
		this.fetchConversations().then(() => {
			// If there are conversations, set the currentConversationId to the ID of the latest one
			if (this.conversations && this.conversations.length > 0) {
				this.currentConversationId = this.conversations[0].id;
				this.fetchChatMessages(this.currentConversationId);
			}
		});
	},
	updated() {
		this.scrollChatToBottom();
	},
	methods: {
		async sendMessage(currentConversationId) {
			const message = this.form.message.trim();

			// Add the user's message to the chat
			if (message) {
					this.messages.push({
					message: this.form.message,
					isUser: true,
				});
			}

			this.form.message = '';

			// Scroll to the bottom
			this.$nextTick(() => {
				this.scrollChatToBottom();
			});

			try {
				// Set isWaitingForResponse to true before making the API request
				this.isWaitingForResponse = true;

				const csrfToken = await fetchCsrfToken();
				const data = await sendMessage(csrfToken, message, this.currentConversationId);

				// If this line is reached, it means the status was 200.
				this.form.message = ''; // Clear textarea
				this.messageError = ''; // Clear error message

				// Find the selected conversation that includes the personality name
				const selectedConversation = this.conversations.find(conv => conv.id === currentConversationId);

				// Create a message object with the chatbot's response
				const messageChatbot = {
					message: data.reply,
					isUser: false,
					personality_name: selectedConversation ? selectedConversation.personality.name : 'Assistant'
				};

				// Set isWaitingForResponse back to false once the response is received
				this.isWaitingForResponse = false;

				// Set focus to the textarea once the DOM has updated
				this.$nextTick(() => {
					this.$refs.chatTextarea.focus();
				});

				this.messages.push(messageChatbot);

				// Scroll to the bottom after receiving the response
				this.$nextTick(() => {
					this.scrollChatToBottom();
				});

				// Fetch the updated message count
				fetch(`/get-messages/${currentConversationId}/message-count`)
					.then(response => response.json())
					.then(data => {
						// Update the message_count attribute of the conversation
						const conversation = this.conversations.find(conv => conv.id === currentConversationId);
						if (conversation) {
							conversation.message_count = data.count;
						}
					})
					.catch(error => {
						// Handle the error if needed
						console.error(error);
					});

			} catch (error) {
				this.isWaitingForResponse = false

				if (error.source === 'frontend') {
          // Handle frontend validation errors
          this.errors = error.errors;
        } else {
          // Handle backend validation errors
          //console.log(error)
          this.errors = normalizeErrors(error || { message: ['An unexpected error occurred.'] });
        }
			}
		},
		async createNewConv() {
			try {

				const csrfToken = await fetchCsrfToken();
				const data = await createNewConversation(csrfToken, this.newConv.title, this.newConv.personality_id);

				// If this line is reached, it means the status was 200.
				this.currentConversationId = data.latestConversation.id;

				// Optionally, you can call the selectConversation method with the ID.
				this.selectConversation(data.latestConversation.id);

				// Fetch the chat messages for the new conversation
				this.fetchChatMessages(this.currentConversationId);

				// Close the modal
				const modalElement = document.getElementById('new-conversation');
				const modalInstance = bootstrap.Modal.getInstance(modalElement);
				if (modalInstance) {
					modalInstance.hide();
				}

				// Fetch updated conversations
				this.fetchConversations();

				// Clear the title input
				this.newConv.title = '';

				// Clear any previous error
				//this.titleError = null;

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
			}
		},
		async selectConversation(conversationId) {
			try {
				const data = await getMessages(conversationId);
				this.messages = data.messages;
				this.currentConversationId = conversationId;

				// Fetch chat messages for the selected conversation
				this.fetchChatMessages(conversationId);

				this.scrollChatToBottom();
			} catch (error) {
				handleError(error);
			}
		},
		async fetchConversations() {
			this.isLoading = true;
			try {
				const data = await getConversations();
				this.conversations = data.allConversations;
				this.isLoading = false;
			} catch (error) {
				handleError(error);
				this.isLoading = false;
			}
		},
		async fetchChatMessages(conversationId) {
			this.isLoading = true;
			try {
				const data = await getMessages(conversationId);
				this.messages = data.chats;
				this.scrollChatToBottom();
				this.isLoading = false;
			} catch (error) {
				handleError(error);
				this.isLoading = false;
			}
		},
		async fetchPersonalities() {
			try {
				const data = await getPersonalities();
				this.personalities = data.personalities;
			} catch (error) {
				handleError(error);
			}
		},
		formatMessage(message) {
			const md = new MarkdownIt();
			return md.render(String(message));
		},
		scrollChatToBottom() {
			this.$nextTick(() => {
				const chatBody = this.$refs.chatBody;
				if (chatBody) {
					const simpleBar = SimpleBar.instances.get(chatBody);
					if (simpleBar) {
						const scrollElement = simpleBar.getScrollElement();
						scrollElement.scrollTop = scrollElement.scrollHeight;
					}
				}
			});
		},
		watchCurrentConversationId() {
			this.$watch('currentConversationId', (newVal) => {
				if (newVal) {
					// Using setTimeout to delay the focus attempt
					setTimeout(() => {
						const chatTextarea = this.$refs.chatTextarea;
						if (chatTextarea) {
							chatTextarea.focus();
						} else {
							console.log("Textarea is still not available in the DOM");
						}
					}, 500); // Delaying by 1 second
				}
			});
		},
		getMessageCount() {
			if (!this.messages) {
				return 0;
			}
			return this.messages.length;
		},
		toggleConversationsList() {
			this.showConversationsList = !this.showConversationsList;
		},
		handleOutsideClick(event) {
			// Check if the click event occurred outside of the conversations list
			if (
				this.$refs.conversationsList &&
				!this.$refs.conversationsList.contains(event.target) &&
				this.$refs.menuBtn &&
				!this.$refs.menuBtn.contains(event.target)
			) {
				this.showConversationsList = false; // Close the conversations list
			}
		},
		formatCreatedAt(date) {
			return moment(date).fromNow();
		},
		clearMessageError() {
			this.messageError = '';
		},
		clearError(field) {
      if (this.errors[field]) {
        this.errors[field] = ''; // Clear the error message
      }
    },
	},
	watch: {
		currentConversationId(newValue, oldValue) {
			if (newValue !== oldValue) {
				setTimeout(() => {
					const chatBody = this.$refs.chatBody;
					//console.log('chatBody within watcher:', chatBody);
					if (chatBody) {
						new SimpleBar(chatBody);
						this.scrollChatToBottom();
					}
				}, 200); // A hacky solution but one must wait
			}
		}
	}
});
</script>