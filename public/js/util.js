function sanitizeMoneyToDigits(value) {
  if (value === null || value === undefined) return '';
  const digits = String(value).replace(/[^\d]/g, '');
  // normalize leading zeros: "00012" -> "12", but keep single "0"
  return digits.replace(/^0+(?=\d)/, '');
}

function formatVndNumber(digits) {
  const normalized = sanitizeMoneyToDigits(digits);
  if (normalized === '') return '';
  const n = parseInt(normalized, 10);
  if (Number.isNaN(n)) return '';
  // Big ecommerce CMS typically show thousand separators without currency symbol inside the input
  return new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(n);
}

function setCaretByDigitCount(inputEl, digitCount) {
  if (!inputEl || typeof inputEl.value !== 'string') return;
  const v = inputEl.value;
  if (digitCount <= 0) {
    inputEl.setSelectionRange(0, 0);
    return;
  }
  let seen = 0;
  for (let i = 0; i < v.length; i++) {
    if (/\d/.test(v[i])) seen++;
    if (seen >= digitCount) {
      inputEl.setSelectionRange(i + 1, i + 1);
      return;
    }
  }
  inputEl.setSelectionRange(v.length, v.length);
}

function formatMoneyInputElement(inputEl) {
  if (!inputEl) return;

  const oldValue = inputEl.value || '';
  const selStart = typeof inputEl.selectionStart === 'number' ? inputEl.selectionStart : oldValue.length;

  // how many digits were to the left of caret?
  const digitsLeftOfCaret = oldValue.slice(0, selStart).replace(/[^\d]/g, '').length;

  const digits = sanitizeMoneyToDigits(oldValue);
  const formatted = formatVndNumber(digits);
  inputEl.value = formatted;

  // keep caret position stable (by digit count)
  try {
    setCaretByDigitCount(inputEl, digitsLeftOfCaret);
  } catch (e) {
    // ignore (some inputs may not support selection in certain browsers)
  }
}

// Backwards compatible handler (supports inline event or direct element usage)
function onlyNumberAmount(eventOrEl) {
  const inputEl = eventOrEl && eventOrEl.target ? eventOrEl.target : eventOrEl;
  formatMoneyInputElement(inputEl);
}

$.validator.addMethod(
  "greaterThan",
  function(value, element, params) {
      let otherField = $(params);
      let otherVal = otherField.val();
      if (!otherVal) otherVal = '0';
      if (!value) value = '0';

      let amountValue = value.replace(/[^0-9]/g, '');
      let amountOtherValue = otherVal.replace(/[^0-9]/g, '');

      amountValue = parseInt(amountValue) || 0;
      amountOtherValue = parseInt(amountOtherValue) || 0;

      // allow equal (matches UX/message: "cao hơn hoặc bằng")
      return amountValue >= amountOtherValue;
  },
  "Value must be greater than {0}."
);

$.validator.addMethod(
  "lessThan",
  function(value, element, params) {
      let otherField = $(params);
      let otherVal = otherField.val();
      if (!otherVal) otherVal = '0';
      if (!value) value = '0';

      let amountValue = value.replace(/[^0-9]/g, '');
      let amountOtherValue = otherVal.replace(/[^0-9]/g, '');

      amountValue = parseInt(amountValue) || 0;
      amountOtherValue = parseInt(amountOtherValue) || 0;

      return amountValue < amountOtherValue;
  },
  "Value must be less than {0}."
);

$.validator.addMethod(
  "minPrice",
  function(value, element, params) {
    console.log("222", value, parseFloat(value), parseFloat(params))
    return parseFloat(value) >= parseFloat(params)
  },
  "Value must be greater than {0}."
);
