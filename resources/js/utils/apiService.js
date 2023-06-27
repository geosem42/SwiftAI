import { handleError } from 'vue';
import { handleApiResponse } from './utils';

async function fetchCsrfToken() {
  try {
    const response = await fetch('/csrf-token');
    const data = await handleApiResponse(response);
    return data.csrfToken;
  } catch (error) {
    throw error;
  }
}

async function createNewConversation(csrfToken, title, personalityId) {
  if (!title || !personalityId) {
    throw {
      errors: {
        ...(!title && { title: ['Please enter a title.'] }),
        ...(!personalityId && { personality_id: ['Please select a personality.'] })
      },
      source: 'frontend'
    };
  }
  try {
    const response = await fetch('/new-conversation', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        title: title,
        personality_id: personalityId,
      }),
    });

    return await handleApiResponse(response);

  } catch (error) {
    throw error;
  }
}

async function sendMessage(csrfToken, message, conversationId) {

  if (!message) {
    throw { errors: { message: ['Please enter a message.'] }, source: 'frontend' };
  }

  try {
    const response = await fetch('/send-message', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        message: message,
        conversation_id: conversationId
      }),
    });

    return await handleApiResponse(response);

  } catch (error) {
    throw error;
  }
}

async function getMessages(conversationId) {
  const response = await fetch(`/get-messages/${conversationId}`);
  return handleApiResponse(response);
}

async function getConversations() {
  const response = await fetch('/get-conversations');
  return handleApiResponse(response);
}

async function getPersonalities() {
  const response = await fetch('/personalities');
  return handleApiResponse(response);
}

async function generateImage(csrfToken, prompt, style, width, height, upscaleImage) {

  if (!prompt) {
    throw { errors: { prompt: ['Please enter a prompt.'] }, source: 'frontend' };
  }

  try {
    const response = await fetch('/generate-image', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        prompt: prompt,
        style: style,
        width: width,
        height: height,
        upscale: upscaleImage,
      }),
    });

    const result = await handleApiResponse(response);

    return {
      original: result.original,
      upscaled: result.upscaled,
      prompt: prompt
    };

  } catch (error) {
    throw error;
  }
}

async function getInitialImages() {
  try {
    const response = await fetch('/get-images');
    return await handleApiResponse(response);
  } catch (error) {
    throw error;
  }
}

async function getMoreImages(nextPageUrl) {
  try {
    const response = await fetch(nextPageUrl);
    return await handleApiResponse(response);
  } catch (error) {
    throw error;
  }
}

async function fetchVoices(csrfToken) {
  try {
    const response = await fetch('/texttospeech/voices', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
    });

    return await handleApiResponse(response);

  } catch (error) {
    throw error;
  }
}

async function generateAudio(csrfToken, language, voice, message) {
  if (!message || !language || !voice) {
    throw {
      errors: {
        ...(!message && { message: ['Please enter your message.'] }),
        ...(!language && { language: ['Please select a language.'] }),
        ...(!voice && { voice: ['Please select a voice.'] })
      },
      source: 'frontend'
    };
  }

  try {
    const response = await fetch('/texttospeech/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        language: language,
        voice: voice,
        message: message
      }),
    });

    return await handleApiResponse(response);

  } catch (error) {
    throw error;
  }
}

async function fetchAudioFiles(page = 1) {
  try {
    const response = await fetch(`/texttospeech/audiofiles?page=${page}`);
    return await handleApiResponse(response);
  } catch (error) {
    throw error;
  }
}

async function uploadDocument(file, csrfToken) {

  if (!file) {
    throw { errors: { file: ['Please select a file to upload.'] }, source: 'frontend' };
  }

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await fetch('/document/upload', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
      },
      body: formData
    });

    return await handleApiResponse(response);
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

async function fetchDocuments(csrfToken, page = 1) {
  try {
    const response = await fetch(`/document/list?page=${page}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
    });

    const data = await handleApiResponse(response);
    return data;

  } catch (error) {
    throw error;
  }
}

async function deleteDocument(documentId, csrfToken) {
  try {
    const response = await fetch(`/document/${documentId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });
    return handleApiResponse(response);
  } catch (error) {
    throw error;
  }
}

async function searchDocument(query, documentId, csrfToken) {
  try {
    const response = await fetch('/document/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ query, document_id: documentId })
    });
    return handleApiResponse(response);
  } catch (error) {
    throw error;
  }
}

async function fetchChatHistory(documentId, csrfToken) {
  try {
    const response = await fetch(`/document/history/${documentId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });
    return handleApiResponse(response);
  } catch (error) {
    throw error;
  }
}

async function uploadAudioForTranscription(file, csrfToken) {

  if (!file) {
    throw { errors: { file: ['Please select a file to upload.'] }, source: 'frontend' };
  }

  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await fetch('/speechtotext/transcribe', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
      },
      body: formData
    });

    return await handleApiResponse(response);
  } catch (error) {
    //console.error('Error:', error);
    throw error;
  }

}

async function fetchTranscriptions(csrfToken, page = 1) {
  const response = await fetch(`/speechtotext/transcriptions?page=${page}`, {
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
    },
  });
  return handleApiResponse(response);
}

export {
  fetchCsrfToken,
  createNewConversation,
  sendMessage,
  getMessages,
  getConversations,
  getPersonalities,
  generateImage,
  getInitialImages,
  getMoreImages,
  fetchVoices,
  generateAudio,
  fetchAudioFiles,
  uploadDocument,
  fetchDocuments,
  deleteDocument,
  searchDocument,
  fetchChatHistory,
  uploadAudioForTranscription,
  fetchTranscriptions
}