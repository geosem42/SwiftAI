export async function handleApiResponse(response) {
  switch (response.status) {
      case 200:
      case 201:
          // When the response is OK
          return await response.json();
      case 400:
          throw new Error('400 Bad Request');
      case 401:
          throw new Error('401 Unauthorized');
      case 403:
          throw new Error('403 Forbidden');
      case 404:
          throw new Error('404 Not Found');
      case 422:
          // Handle validation errors
          const errorData = await response.json();
          //console.log('Validation Error Data:', errorData);
          throw errorData;
      case 500:
          //throw new Error('500 Internal Server Error');
          throw await response.json();
      default:
          throw new Error(`Received unexpected status code: ${response.status}`);
  }
}