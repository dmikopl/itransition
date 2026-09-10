import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['activityDate', 'bookingDate', 'formError'];

    connect() {
        this.syncDateBounds();
    }

    syncDateBounds() {
        if (!this.hasActivityDateTarget || !this.hasBookingDateTarget) {
            return;
        }

        const activityDate = this.activityDateTarget.value;
        const bookingDate = this.bookingDateTarget.value;

        if (activityDate) {
            this.bookingDateTarget.max = activityDate;
        } else {
            this.bookingDateTarget.removeAttribute('max');
        }

        if (bookingDate) {
            this.activityDateTarget.min = bookingDate;
        } else {
            this.activityDateTarget.removeAttribute('min');
        }
    }

    validate(event) {
        this.clearBanner();
        this.clearFieldErrors();

        const messages = [];

        if (this.hasActivityDateTarget && !this.activityDateTarget.value) {
            messages.push('Activity date is required.');
            this.markInvalid(this.activityDateTarget, 'Activity date is required.');
        }

        if (this.hasBookingDateTarget && !this.bookingDateTarget.value) {
            messages.push('Booking date is required.');
            this.markInvalid(this.bookingDateTarget, 'Booking date is required.');
        }

        if (
            this.hasActivityDateTarget
            && this.hasBookingDateTarget
            && this.activityDateTarget.value
            && this.bookingDateTarget.value
            && this.bookingDateTarget.value > this.activityDateTarget.value
        ) {
            const message = 'Booking date must be on or before the activity date.';
            messages.push(message);
            this.markInvalid(this.bookingDateTarget, message);
        }

        this.element.querySelectorAll('input[name*="[price]"]').forEach((input) => {
            const value = Number(input.value);
            if (input.value === '' || Number.isNaN(value) || value <= 0) {
                const message = 'Ticket price must be greater than 0.';
                messages.push(message);
                this.markInvalid(input, message);
            }
        });

        this.element.querySelectorAll('input[name*="[name]"]').forEach((input) => {
            if (input.value.trim() === '') {
                const message = 'Ticket category name is required.';
                messages.push(message);
                this.markInvalid(input, message);
            }
        });

        if (messages.length > 0) {
            event.preventDefault();
            this.showBanner(messages);
            const firstInvalid = this.element.querySelector('[aria-invalid="true"]');
            firstInvalid?.focus();
        }
    }

    markInvalid(input, message) {
        input.setAttribute('aria-invalid', 'true');
        input.classList.add('is-invalid');

        const row = input.closest('.form-row') ?? input.parentElement;
        if (!row) {
            return;
        }

        row.classList.add('has-error');

        let list = row.querySelector('ul.form-errors');
        if (!list) {
            list = document.createElement('ul');
            list.className = 'form-errors';
            input.insertAdjacentElement('beforebegin', list);
        }

        const item = document.createElement('li');
        item.className = 'form-error';
        item.textContent = message;
        list.appendChild(item);
    }

    clearFieldErrors() {
        this.element.querySelectorAll('.is-invalid').forEach((input) => {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        });
        this.element.querySelectorAll('.form-row.has-error').forEach((row) => {
            row.classList.remove('has-error');
        });
        this.element.querySelectorAll('ul.form-errors').forEach((list) => {
            list.remove();
        });
    }

    showBanner(messages) {
        if (!this.hasFormErrorTarget) {
            return;
        }

        this.formErrorTarget.hidden = false;
        this.formErrorTarget.innerHTML = '';
        const title = document.createElement('strong');
        title.textContent = 'Please fix the form errors:';
        this.formErrorTarget.appendChild(title);

        const list = document.createElement('ul');
        [...new Set(messages)].forEach((message) => {
            const item = document.createElement('li');
            item.textContent = message;
            list.appendChild(item);
        });
        this.formErrorTarget.appendChild(list);
    }

    clearBanner() {
        if (!this.hasFormErrorTarget) {
            return;
        }

        this.formErrorTarget.hidden = true;
        this.formErrorTarget.innerHTML = '';
    }
}
