$(function () {
    $('#resetBtn').on('click', function () {
        // フォームの入力値をリセット（ブラウザ側）
        $('#calcForm')[0].reset();

        // 計算結果を非表示にする
        $('#resultSection').hide();

        // ページをGETでリロードしPOST値をクリア（PHPが空の値を表示）
        location.href = location.pathname;
    });
});
