<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>人事管理表</title>
    </head>

    <body>
        <h1>人事管理表</h1>
<!-- 所属社員の検索欄 -->
        <form>
        検索：<input type="submit" name="keyword">
        </form>
<!-- 所属社員の表示欄 -->
        <table border="1">
            <tr>
                <tr>社員番号</tr>
                    <th><!--データベース情報--></th>
                <tr>氏名</tr>
                    <th><!--データベース情報--></th>
                <tr>所属部署</tr>
                    <th><!--データベース情報--></th>
                <tr>職位</tr>
                    <th><!--データベース情報--></th>
                <tr>勤怠管理</tr>
                    <th><!--データベース情報(シフト表的なもの)--></th>
                <tr>勤怠状況</tr>
                    <th><!--データベース情報(出勤・退勤)--></th>
            </tr>
        </table>
    </body>
</html>
