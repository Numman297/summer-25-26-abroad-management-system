/* ================================================================
   SHARED JAVASCRIPT
   1. validateForm()  - client side validation before a form is sent
   2. esc()           - escapes text before it is put into the page
   3. liveSearch()    - waits until typing stops, then runs a search
   4. Table filter    - instant DOM filtering for data tables
   5. Live stats auto refresh
   ================================================================ */

/* ---------------- 1. Form validation ---------------- */

function showFieldError(input, message) {
    input.classList.add('is-invalid');
    var note = document.createElement('span');
    note.className = 'field-error';
    note.textContent = message;
    if (input.parentNode) {
        input.parentNode.appendChild(note);
    }
}

function clearFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });
    form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
}

function validateForm(form) {
    clearFieldErrors(form);
    var valid = true;
    var firstBad = null;

    form.querySelectorAll('input, select, textarea').forEach(function (input) {
        if (input.type === 'hidden' || input.type === 'checkbox' || input.disabled) { return; }

        var value = (input.value || '').trim();
        var label = input.getAttribute('data-label') || input.name || 'This field';
        var message = '';

        if (input.hasAttribute('required') && value === '') {
            message = label + ' is required.';

        } else if (value !== '' && input.dataset.min && value.length < parseInt(input.dataset.min, 10)) {
            message = label + ' needs at least ' + input.dataset.min + ' characters.';

        } else if (value !== '' && input.type === 'email' &&
                   !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value)) {
            message = 'Enter a valid email address.';

        } else if (value !== '' && input.dataset.phone &&
                   !/^[0-9+\-\s()]{6,20}$/.test(value)) {
            message = 'Enter a valid contact number.';

        } else if (value !== '' && input.dataset.match) {
            var other = form.querySelector('[name="' + input.dataset.match + '"]');
            if (other && value !== other.value.trim()) {
                message = 'The two passwords do not match.';
            }

        } else if (value !== '' && input.type === 'number') {
            var num = parseFloat(value);
            if (isNaN(num)) {
                message = label + ' must be a number.';
            } else if (input.min !== '' && num < parseFloat(input.min)) {
                message = label + ' cannot be less than ' + input.min + '.';
            } else if (input.max !== '' && num > parseFloat(input.max)) {
                message = label + ' cannot be more than ' + input.max + '.';
            }
        }

        if (message) {
            showFieldError(input, message);
            valid = false;
            if (!firstBad) { firstBad = input; }
        }
    });

    if (firstBad) { firstBad.focus(); }
    return valid;
}

/* ---------------- 2. Escaping ---------------- */

function esc(text) {
    return String(text === null || text === undefined ? '' : text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* ---------------- 3. Live Table DOM Filter ---------------- */

function setupTableFilter(inputId, tableId) {
    var input = document.getElementById(inputId);
    var table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('keyup', function () {
        var filter = input.value.toLowerCase();
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    setupTableFilter('userSearch', 'usersTable');
    setupTableFilter('appSearch', 'appTable');
    setupTableFilter('progSearch', 'exploreProgTable');

    // Auto refresh live stats every 15 seconds if on a dashboard
    if (document.getElementById('statGrid') && window.APP_ROLE) {
        setInterval(function () {
            fetch('index.php?page=ajax&action=stats')
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && !data.error) {
                        for (var key in data) {
                            var el = document.querySelector('[data-stat="' + key + '"]');
                            if (el) {
                                if (key.indexOf('revenue') > -1 || key.indexOf('earnings') > -1) {
                                    el.textContent = '$' + parseFloat(data[key]).toFixed(2);
                                } else if (key.indexOf('rate') > -1) {
                                    el.textContent = parseFloat(data[key]).toFixed(1) + '%';
                                } else {
                                    el.textContent = data[key];
                                }
                            }
                        }
                    }
                })
                .catch(function () {});
        }, 15000);
    }
});
