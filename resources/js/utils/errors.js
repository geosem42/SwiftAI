class CustomError extends Error {
  constructor(message) {
      super(message);
      this.name = this.constructor.name;
  }
}

class ApiError extends CustomError {
  constructor(message, status) {
      super(message);
      this.status = status;
  }
}

export { CustomError, ApiError };