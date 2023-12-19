let csrfToken;

async function fetchCsrfToken() {
    const response = await fetch('engine/getCsrfToken.php');
    const data = await response.json();
    csrfToken = data.csrf_token;
}

fetchCsrfToken();

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('editProfileForm');
    form.addEventListener('submit', handleFormSubmit);

    $("[data-fancybox]").fancybox({});
});

function handleFormSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    fetch('engine/editProfile.php', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': csrfToken,
        },
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Изменено!');
            } else if (data.error) {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
}
