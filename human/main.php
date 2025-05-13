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
                <th>社員番号</th>
                <th>氏名</th>
                <th>所属部署</th>
            </tr>
        </table>
