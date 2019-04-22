import { priceTypes } from '../priceTypes';
import { HistoryConfig } from '../types';
import { KeyValidationStatus } from './types';

function validatePriceType(priceType: HistoryConfig['priceType']) {
  const status: KeyValidationStatus = { isValid: true, validationErrors: [] };

  if (!priceTypes.hasOwnProperty(priceType)) {
    status.isValid = false;
    status.validationErrors.push(`Price type "${priceType}" is not supported`);
  }
  return status;
}

export { validatePriceType };
