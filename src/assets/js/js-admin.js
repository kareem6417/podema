document.addEventListener('DOMContentLoaded', function () {
    // Fungsi untuk menangani input pencarian
    function handleSearchInput() {
        var searchInput = document.getElementById('search');
        var searchLabel = document.querySelector('.search-label');

        if (searchInput.value.trim() !== '') {
            searchLabel.style.display = 'none';
        } else {
            searchLabel.style.display = 'block';
        }
    } 

    // Tambahkan event listener oninput pada elemen pencarian
    var searchInput = document.getElementById('search');
    searchInput.addEventListener('input', function () {
        handleSearchInput();
        searchUsers();
    });

    // Tambahkan event listener onchange pada elemen select limit
    var limitSelect = document.getElementById('limit');
    limitSelect.addEventListener('change', function () {
        setTableRowsPerPage();
    });

    // Inisialisasi setTableRowsPerPage saat halaman dimuat
    setTableRowsPerPage();
});

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

