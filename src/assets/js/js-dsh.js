$(document).ready(function () {
    $(".popup-trigger").click(function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var company = $(this).data('company');
        var type = $(this).data('type');
        var serialnumber = $(this).data('serialnumber');
        var os = $(this).data('os_name');
        var osScore = $(this).data('os_score');
        var processor = $(this).data('processor_name');
        var processorScore = $(this).data('processor_score');
        var batterylife = $(this).data('battery_name');
        var batterylifeScore = $(this).data('battery_score');
        var age = $(this).data('age_name');
        var ageScore = $(this).data('age_score');
        var issue = $(this).data('issue_name');
        var issueScore = $(this).data('issue_score');
        var ram = $(this).data('ram_name');
        var ramScore = $(this).data('ram_score');
        var storage = $(this).data('storage_name');
        var storageScore = $(this).data('storage_score');
        var keyboard = $(this).data('keyboard_name');
        var keyboardScore = $(this).data('keyboard_score');
        var screen = $(this).data('screen_name');
        var screenScore = $(this).data('screen_score');
        var touchpad = $(this).data('touchpad_name');
        var touchpadScore = $(this).data('touchpad_score');
        var audio = $(this).data('audio_name');
        var audioScore = $(this).data('audio_score');
        var body = $(this).data('body_name');
        var bodyScore = $(this).data('body_score');
        var score = $(this).data('score');

        $("#popup-id").text(id);
        $("#popup-name").text(name);
        $("#popup-company").text(company);
        $("#popup-type").text(type);
        $("#popup-serialnumber").text(serialnumber);
        $("#popup-os_name").text(os + " \t (" + osScore + ")");
        $("#popup-processor_name").text(processor + " \t (" + processorScore + ")");
        $("#popup-batterylife_name").text(batterylife + " \t (" + batterylifeScore + ")");
        $("#popup-age_name").text(age + " \t (" + ageScore + ")");
        $("#popup-issue_name").text(issue + " \t (" + issueScore + ")");
        $("#popup-ram_name").text(ram + " \t (" + ramScore + ")");
        $("#popup-storage_name").text(storage + " \t (" + storageScore + ")");
        $("#popup-keyboard_name").text(keyboard + " \t (" + keyboardScore + ")");
        $("#popup-screen_name").text(screen + "\t (" + screenScore + ")");
        $("#popup-touchpad_name").text(touchpad + "\t (" + touchpadScore + ")");
        $("#popup-audio_name").text(audio + "\t (" + audioScore + ")");
        $("#popup-body_name").text(body + "\t (" + bodyScore + ")");
        $("#popup-score").text(score);
        $(".popup-overlay, .popup-content").fadeIn();
    });

    $(".popup-overlay").click(function () {
        $(".popup-overlay, .popup-content").fadeOut();
    });
});