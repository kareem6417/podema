document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('name').addEventListener('change', function() {
        var selectedName = this.value;

        if (selectedName in userInfos) {
            document.getElementById('company').value = userInfos[selectedName].company;
            document.getElementById('divisi').value = userInfos[selectedName].divisi;
        } else {
            document.getElementById('company').value = '';
            document.getElementById('divisi').value = '';
        }
    });
});