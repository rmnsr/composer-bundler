$(document).ready(() => {
    const $jsonInput = $('#jsonInput');
    const $generateBtn = $('#generateBtn');
    const $loader = $('#loader');

    $generateBtn.on('click', () => {
        const json = $jsonInput.val();

        $.ajax({
            url: 'index.php?p=ajax&action=generate',
            method: 'POST',
            data: { json },
            dataType: 'json',
            beforeSend: () => {
                $loader.show();
                $generateBtn.hide();
            },
            success: (data) => {
                console.log(data);
                if (data.file) {
                    window.location.href = `index.php?p=ajax&action=download&file=${encodeURIComponent(data.file)}`;
                }
                
                if(data.error) {
                    alert(data.error);
                }
            },
            error: (xhr, status, error) => {
                console.error('AJAX Error:', status, error, xhr.responseText);
            },
            complete: () => {
                $loader.hide();
                $generateBtn.show();
            }
        });
    });
});
