function toggleInspeksi(count) {
        var content = document.getElementById("inspeksi-" + count);
        if (content.style.display === "none") {
            content.style.display = "block";
        } else {
            content.style.display = "none";
        }
    }

    function toggleAssessment(count) {
        var content = document.getElementById("assessment-" + count);
        if (content.style.display === "none") {
            content.style.display = "block";
        } else {
            content.style.display = "none";
        }
    }

    function toggleAssessmentPC(assessmentNumber) {
        var assessmentContent = document.getElementById('assessment-pc-' + assessmentNumber);
        if (assessmentContent.style.display === 'none') {
            assessmentContent.style.display = 'block';
        } else {
            assessmentContent.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
    var submenuItems = document.querySelectorAll('.sidebar-submenu');

    submenuItems.forEach(function(item) {
        item.style.maxHeight = '0';

        item.closest('.sidebar-item').addEventListener('click', function(e) {
            e.preventDefault();

            this.classList.toggle('active');

            submenuItems.forEach(function(subitem) {
                if (subitem !== item) {
                    subitem.style.maxHeight = '0';
                }
            });

            if (this.classList.contains('active')) {
                item.style.maxHeight = '1000px';
            } else {
                item.style.maxHeight = '0';
            }
        });

        var submenuLinks = item.querySelectorAll('.sidebar-link');
        submenuLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
});