/**
 * Validates JSON string and returns validation result
 */
export interface JsonValidationResult {
  isValid: boolean;
  error?: string;
  parsedData?: any;
}

/**
 * Validates a JSON string and returns detailed validation information
 */
export function validateJson(jsonString: string): JsonValidationResult {
  if (!jsonString.trim()) {
    return {
      isValid: true,
      parsedData: {},
    };
  }

  try {
    const parsed = JSON.parse(jsonString);
    return {
      isValid: true,
      parsedData: parsed,
    };
  } catch (error) {
    return {
      isValid: false,
      error: error instanceof Error ? error.message : 'Invalid JSON format',
    };
  }
}

/**
 * Formats JSON string with proper indentation
 */
export function formatJson(jsonString: string): string {
  const validation = validateJson(jsonString);
  if (!validation.isValid) {
    return jsonString; // Return original if invalid
  }

  try {
    return JSON.stringify(validation.parsedData, null, 2);
  } catch {
    return jsonString;
  }
}

/**
 * Checks if a JSON string is valid without parsing
 */
export function isJsonValid(jsonString: string): boolean {
  return validateJson(jsonString).isValid;
}
