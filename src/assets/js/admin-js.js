function searchUsers() {
    var searchKeyword = document.getElementById("search").value;
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var searchResults = JSON.parse(xhr.responseText);

            var tableBody = document.querySelector("table tbody");
            tableBody.innerHTML = "";

            if (searchResults.length > 0) {
                for (var i = 0; i < searchResults.length; i++) {
                    var user = searchResults[i];
                    var row = document.createElement("tr");
                    row.innerHTML = "<td>" + user.username + "</td>" +
                                    "<td>" + user.name + "</td>" +
                                    "<td>" + user.nik + "</td>" +
                                    "<td>" + user.company + "</td>" +
                                    "<td>" + user.department + "</td>" +
                                    "<td style='text-align: center;'>" +
                                    "<a class='edit-icon' style='margin-right: 10px;' href='user_detail.php?user_id=" + user.user_id + "&name=" + encodeURIComponent(user.name) + "'><i class='fas fa-edit'></i></a>" +
                                    "<a class='delete-user-button' href='delete_user.php?user_id=" + user.user_id + "'><i class='fas fa-trash'></i></a>" +
                                    "</td>";
                    tableBody.appendChild(row);
                }
            } else {
                // Menampilkan pesan jika tidak ada hasil
                tableBody.innerHTML = "<tr><td colspan='6'>No users found</td></tr>";
            }
        }
    };
    xhr.open("GET", "search_users.php?keyword=" + encodeURIComponent(searchKeyword), true);
    xhr.send();
}

function handleSearchInput() {
    var searchInput = document.getElementById('search');
    var searchLabel = document.querySelector('.search-label');

    if (searchInput.value !== '') {
        searchInput.classList.add('not-empty');
        searchLabel.style.display = 'none';
    } else {
        searchInput.classList.remove('not-empty');
        searchLabel.style.display = 'block';
    }
}

function openUserDetailsPopup(userId) {
    window.location.href = 'user_detail.php?user_id=' + userId;
} 

function showPopup() {
    var popup = document.getElementById("popup");
    popup.style.display = "block";
}

function closePopup() {
    var popup = document.getElementById("popup");
    popup.style.display = "none";
}

function displayNotification(message) {
    var notification = document.getElementById("notification");
    notification.textContent = message;
    notification.style.display = "block";
    setTimeout(function() {
        notification.style.display = "none";
    }, 3000);
}

function closeUserDetailsPopup() {
    document.getElementById("userDetailsPopup").style.display = "none";
}

function showSuccessPopup() {
var script = document.createElement("script");
script.innerHTML = `
    window.onload = function() {
        var notification = document.getElementById('notification');
        notification.textContent = 'Successfully! New user has been created.';
        notification.style.display = 'block';
        setTimeout(function() {
            notification.style.display = 'none';
        }, 3000);
    }
`;
document.body.appendChild(script);
}

function deleteUser(userId) {
    var confirmDelete = confirm("Apakah kamu yakin ingin menghapus pengguna tersebut?");
    if (confirmDelete) {
        window.location.href = "delete_user.php?user_id=" + userId;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var deleteButtons = document.querySelectorAll('.delete-user-button');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var userId = button.getAttribute('data-user-id');
            deleteUser(userId);
        });
    });
});

function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var deleteButtons = document.querySelectorAll('.delete-user-button');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var userId = button.getAttribute('data-user-id');
                deleteUser(userId);
            });
        });

        var scrollToTopButton = document.querySelector('.scroll-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                scrollToTopButton.style.display = 'block';
            } else {
                scrollToTopButton.style.display = 'none';
            }
        });
    });