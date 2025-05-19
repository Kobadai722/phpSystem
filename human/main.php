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
        検索：  <input type="text"  name="keyword">
                <input type="submit" value="検索">
        </form>
<!-- 所属社員の表示欄 -->
        <table border="1">
            <tr>
                <th>社員番号</th>
                <td><!--データベース情報--></td>
            </tr>
            <tr>
                <th>氏名</th>
                <td><!--データベース情報--></td>
            </tr>
            <tr>
                <th>所属部署</th>
                <td><!--データベース情報--></td>
            </tr>
            <tr>
                <th>職位</th>
                <td><!--データベース情報--></td>
            </tr>
            <tr>
                <th>勤怠管理</th>
                <td><!--データベース情報(シフト表的なもの)--></td>
            </tr>
            <tr>
                <th>勤怠状況</th>
                <td><!--データベース情報(出勤・退勤)--></td>
            </tr>
        </table>
    </body>
</html>
