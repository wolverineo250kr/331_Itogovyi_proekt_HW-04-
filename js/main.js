let csrfToken;

async function fetchCsrfToken() {
    const response = await fetch('engine/getCsrfToken.php');
    const data = await response.json();
    csrfToken = data.csrf_token;

    $('.csfrD').val(csrfToken);
}

fetchCsrfToken();

function searchContacts() {
    const nickname = document.getElementById("nickname").value;

    if (nickname.trim() == "") {
        alert("Поле ввода пустое");
        return;
    }

    fetch(`engine/search.php?nickname=${nickname}`)
        .then(response => response.json())
        .then(data => {
            displayResult(data);
        })
        .catch(error => console.error('Ошибка:', error));
}

function displayResult(contacts) {
    const resultDiv = document.getElementById("result");
    resultDiv.innerHTML = "";

    if (contacts.error) {
        resultDiv.innerHTML = `<p>Ошибка: ${contacts.error}</p>`;
        return;
    }

    if (contacts.length === 0) {
        resultDiv.innerHTML = "<p>никого не нашли</p>";
        return;
    }

    const ul = document.createElement("ul");
    contacts.forEach(contact => {
        const li = document.createElement("li");
        li.textContent = `ID: ${contact.id}, Email: ${contact.email}, Nickname: ${contact.nickname}`;

        li.setAttribute("data-id", contact.id);

        li.addEventListener("click", createChat);

        ul.appendChild(li);
    });

    resultDiv.appendChild(ul);
}

function createChat(event) {
    const contactId = event.currentTarget.getAttribute("data-id");

    fetch(`/engine/createChat.php?contactId=${contactId}`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideSearchResults();
                fetchChats();
            }

        })
        .catch(error => console.error('Ошибка:', error));
}

function hideSearchResults() {
    const resultDiv = document.getElementById("result");
    resultDiv.innerHTML = "";
}

async function fetchChats() {
    try {
        const response = await fetch('engine/getChats.php');
        const data = await response.json();

        return data || [];
    } catch (error) {
        console.error('Ошибка:', error);
        return [];
    }
}

function updateListUsers(commonChats) {
    const listUsers = document.getElementById('listUsers');

    listUsers.innerHTML = '';

    commonChats.forEach(chat => {
        const avatarSrc = chat.avatar ? chat.avatar : '/images/no_image.png';

        const listItem = document.createElement('li');
        listItem.innerHTML = `<a href="#" class="person" data-attr-chat-id="${chat.id}"><img src="${avatarSrc}" width="22" height="22"><span>${chat.other_user_nickname}</span></a>`;
        listUsers.appendChild(listItem);
    });
}

fetchChats()
    .then(data => {
        if (data.success) {
            const commonChats = data.commonChats.map(chat => ({
                id: chat.id,
                name: chat.name,
                avatar: chat.avatar,
                other_user_nickname: chat.other_user_nickname,
            }));
            updateListUsers(commonChats);

            const links = document.querySelectorAll('#listUsers a');
            links[0].classList.add('active');

            longPolling();
        } else {
            console.error('Error updating list users:', data.error || 'Unknown error');
        }
    })
    .catch(error => console.error('Ошибка:', error));

$('body').on('click', '.person', function (e) {
    e.preventDefault();
    $('body').find('.person').removeClass('active');
    $(this).addClass('active');
    longPolling();
})

function sendMessage() {
    const messageContent = document.getElementById('messageInput').value;

    if (!messageContent.trim()) {
        alert('Please enter a message.');
        return;
    }

    const links = document.querySelectorAll('#listUsers a');

    const activeLink = document.querySelector('#listUsers a.active');

    if (activeLink) {
        var selectedChatId = activeLink.getAttribute('data-attr-chat-id');
    } else {
        console.error('Не успех');
    }

    fetch('engine/saveMessage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify({
            chatId: selectedChatId,
            content: messageContent,
        }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateChat(selectedChatId);
            } else {
                alert('Ошибка.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка');
        });

    document.getElementById('messageInput').value = '';
}

function updateChat(chatId) {
    if (typeof chatId === 'undefined') {
        return;
    }
    document.getElementById('chatMessages').innerHTML = '';

    fetch(`engine/getMessages.php?chatId=${chatId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            } else {
                console.error('Ошибка', data.error || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Ошибка', error);
        });
}

function displayMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');

    const activeLink = document.querySelector('#listUsers a.active');

    messages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message-line');

        if (activeLink) {
            const textOfActiveLink = activeLink.textContent;

            if (message.nickname !== textOfActiveLink) {
                messageDiv.classList.add('me');
            }
        }

        messageDiv.setAttribute('data-message-id', message.id);

        $('body').find('#whoisChat').text('Чат с пользователем ' + $('body').find('.person.active').text());

        messageDiv.innerHTML = `<sub>${message.nickname} (<i>${message.email}</i>)</sub><br/><span class="kottekst">${message.content}</span><br/><span class="timestamp">${message.created_at}</span>`;
        chatMessages.appendChild(messageDiv);
    });

    chatMessages.scrollTop = chatMessages.scrollHeight;
}

$(document).ready(function () {
    $(window).scroll(function () {
        $('body').find('.message').remove();

        $('#chatMessages').find('.message').remove();
    });

    $('#chatMessages').scroll(function () {
        $('body').find('.message').fadeOut(300, function () {
            $(this).remove();
        });

        $('#chatMessages').find('.message').fadeOut(300, function () {
            $(this).remove();
        });
    });
});

document.addEventListener('contextmenu', function (event) {
    $('body').find('.message').remove();
    event.preventDefault();
    var x = event.clientX;
    var y = event.clientY;

    const clickedElement = event.target;
    if (clickedElement.classList.contains('message-line') && clickedElement.classList.contains('me')) {
        const messageId = clickedElement.getAttribute('data-message-id');

        var contMenuHTML = '<div class="message" style="position: absolute; left:' + x + 'px; top:' + y + 'px;" data-message-id="' + messageId + '">\n' +
            '    <div class="message-options">\n' +
            '        <a href="#" class="forward-message">Переслать сообщение</a>\n' +
            '        <a href="#" class="edit-message">Отредактировать</a>\n' +
            '        <input type="text" class="edit-line" id="messageInputEdit" style="display: none;"><button onclick="saveEditedMessage(' + messageId + ', $(\'body\').find(\'#messageInputEdit\').val())" style="display: none;" class="edit-line">готово</button>\n' +
            '        <a href="#" class="delete-message">Удалить</a>\n' +
            '    </div>\n' +
            '</div>\n';

        var tempContainer = document.createElement('div');

        tempContainer.innerHTML = contMenuHTML;

        document.body.appendChild(tempContainer.firstChild);
    }
});

let previousMessages = [];

function longPolling() {
    const links = document.querySelectorAll('#listUsers a');

    const activeLink = document.querySelector('#listUsers a.active');

    if (activeLink) {
        var selectedChatId = activeLink.getAttribute('data-attr-chat-id');
    }
    fetch(`engine/getMessages.php?chatId=${selectedChatId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!messagesAreEqual(data.messages, previousMessages)) {
                    document.getElementById('chatMessages').innerHTML = '';
                    displayMessages(data.messages);

                    previousMessages = data.messages;
                }
            } else {
                console.error('Ошибка:', data.error || 'Ошибка неизвесная');
            }

            setTimeout(longPolling, 5000);
        })
        .catch(error => {
            console.error('Ошибка:', error);

            setTimeout(longPolling, 5000);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    longPolling();
});

function messagesAreEqual(messages1, messages2) {
    if (messages1.length !== messages2.length) {
        return false;
    }

    for (let i = 0; i < messages1.length; i++) {
        if (messages1[i].id !== messages2[i].id || messages1[i].content !== messages2[i].content) {
            return false;
        }
    }

    return true;
}

const messageInput = document.getElementById('messageInput');

messageInput.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
        event.preventDefault();

        sendMessage();
    }
});

function deleteMessage(messageId) {
    fetch(`engine/deleteMessage.php?messageId=${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('body').find(`.message[data-message-id="${messageId}"]`).remove();

            } else {
                console.error('Ошибка', data.error || 'Ошибка неизвесности');
            }
        })
        .catch(error => {
            console.error('Ошибка', error);
        });
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-message')) {
        const messageElement = event.target.closest('[data-message-id]');
        const messageId = messageElement.getAttribute('data-message-id');

        const isConfirmed = confirm('Вы уверены, что хотите удалить это сообщение?');

        if (isConfirmed) {
            deleteMessage(messageId);
        }
    }

    if (event.target.classList.contains('edit-message')) {
        const messageElement = event.target.closest('[data-message-id]');
        const messageId = messageElement.getAttribute('data-message-id');

        var messageLine = document.querySelector('.message-line[data-message-id="' + messageId + '"]');

        if (messageLine) {
            var kottekstElement = messageLine.querySelector('.kottekst');

            if (kottekstElement) {
                var textFromKottekst = kottekstElement.textContent;

                document.getElementById('messageInputEdit').value = textFromKottekst;
                $('body').find('.edit-line').slideDown();
                document.getElementById('messageInputEdit').style.display = 'block';

            }
        }
    }
});

function saveEditedMessage(messageId, newContent) {
    if (newContent.length === 0) {
        alert('введите сообщение');
        return;
    }
    fetch('engine/editMessage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify({
            messageId: messageId,
            newContent: newContent,
        }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var editLines = document.querySelectorAll('body .edit-line');
                editLines.forEach(function (editLine) {
                    editLine.style.display = 'none';
                });

                var messages = document.querySelectorAll('body .message');
                messages.forEach(function (message) {
                    message.style.display = 'none';
                    message.addEventListener('transitionend', function () {
                        this.remove();
                    });
                });
            } else {
                console.error('Ошибка ', data.error || 'Ошибка неизвесности');
            }
        });
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('forward-message')) {
        const messageElement = event.target.closest('[data-message-id]');
        const messageId = messageElement.getAttribute('data-message-id');

        showModal(messageId);
    }
});

let isModalVisible = false;

function showModal(messageId) {
    if (isModalVisible) {
        console.log('Модальное окно уже отображено');
        return;
    }

    const modal = document.createElement('div');
    modal.classList.add('modal');

    modal.innerHTML = `
        <h2>Кому отправить?</h2>
        <select id="selectChat1"></select>
        <button id="sendButton1" data-message-idf="${messageId}" onclick="sendModal(${messageId}, $('body').find('#selectChat1').val())">Переслать</button>
    `;

    document.body.appendChild(modal);

    isModalVisible = true;

    var userList = document.getElementById('listUsers');

    var dropdown = document.getElementById('selectChat1');
    userList.querySelectorAll('.person').forEach(function (person) {
        var chatId = person.getAttribute('data-attr-chat-id');
        var name = person.querySelector('span').textContent;

        var option = document.createElement('option');
        option.value = chatId;
        option.text = name;

        if (person.classList.contains('active')) {
            option.selected = true;
        }

        dropdown.appendChild(option);
    });

}

function sendModal(messageId, forwarderChatId) {
    hideModal();

    fetch('engine/forwardMessage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify({
            messageId: messageId,
            forwarderChatId: forwarderChatId,
        }),
    })
        .then(response => response.json())
        .then(data => {

        });

    alert('отправелно');
}

function hideModal() {
    isModalVisible = false;
    const modal = document.querySelector('.modal');
    if (modal) {
        $('body').find('.modal').remove();
    }
}

document.addEventListener('click', function (event) {
    var resultDiv = document.getElementById('result');

    if (event.target !== resultDiv && !resultDiv.contains(event.target)) {
        resultDiv.innerHTML = '';
    }
});
