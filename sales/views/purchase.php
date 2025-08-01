<?php
require_once '../../config.php'; // データベース接続設定ファイルを読み込む
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文管理システム - 注文一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iu6KU6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/styles.css"> </head>
<body>
    <?php include '../../header.php'; ?>
    <main>
        <?php include '../includes/localNavigation.php'; ?>

        <section class="content">
            <div class="container-fluid">
                <h1 class="mb-4">注文一覧</h1>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">検索・フィルタリング</h5>
                        <form class="row g-3 align-items-end" id="searchForm">
                            <div class="col-md-3">
                                <label for="orderId" class="form-label">注文ID</label>
                                <input type="text" class="form-control" id="orderId" placeholder="例: ORD001">
                            </div>
                            <div class="col-md-3">
                                <label for="customerName" class="form-label">顧客名</label>
                                <input type="text" class="form-control" id="customerName" placeholder="例: 山田太郎">
                            </div>
                            <div class="col-md-3">
                                <label for="paymentStatus" class="form-label">支払い状況</label>
                                <select class="form-select" id="paymentStatus">
                                    <option value="">全て</option>
                                    <option value="未払い">未払い</option>
                                    <option value="支払い済み">支払い済み</option>
                                    </select>
                            </div>
                            <div class="col-md-3">
                                <label for="deliveryStatus" class="form-label">配送状況</label>
                                <select class="form-select" id="deliveryStatus">
                                    <option value="">全て</option>
                                    <option value="未発送">未発送</option>
                                    <option value="発送済み">発送済み</option>
                                    </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="searchButton">
                                    <i class="bi bi-search"></i> 検索
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2" id="resetButton">
                                    <i class="bi bi-arrow-counterclockwise"></i> リセット
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <a href="order_detail_edit.php?mode=new" class="btn btn-success" id="newOrderButton">
                        <i class="bi bi-plus-circle"></i> 新規注文登録
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered bg-white shadow-sm rounded">
                        <thead class="table-light">
                            <tr>
                                <th>注文ID</th>
                                <th>注文日時</th>
                                <th>顧客名</th>
                                <th>合計金額</th>
                                <th>支払い状況</th>
                                <th>配送状況</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ordersTableBody = document.getElementById('ordersTableBody');
            const searchForm = document.getElementById('searchForm');
            const resetButton = document.getElementById('resetButton');

            // 注文データをAPIから取得し、テーブルに表示する関数
            async function fetchOrders(params = {}) {
                ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">データを読み込み中...</td></tr>';
                try {
                    const queryParams = new URLSearchParams(params).toString();
                    // ファイルツリーに合わせてパスを修正
                    const response = await fetch(`../api/get_orders_api.php?${queryParams}`);
                    const data = await response.json();

                    ordersTableBody.innerHTML = ''; // 既存の行をクリア

                    if (data.success && data.orders.length > 0) {
                        data.orders.forEach(order => {
                            const row = document.createElement('tr');
                            const orderDatetime = new Date(order.order_datetime);
                            const formattedDatetime = orderDatetime.toLocaleString('ja-JP', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit'
                            }).replace(/\//g, '/');
                            const formattedAmount = '¥' + Number(order.total_amount).toLocaleString();

                            row.innerHTML = `
                                <td>${escapeHTML(order.order_id)}</td>
                                <td>${escapeHTML(formattedDatetime)}</td>
                                <td>${escapeHTML(order.customer_name)}</td>
                                <td>${escapeHTML(formattedAmount)}</td>
                                <td>${escapeHTML(order.status)}</td>
                                <td>${escapeHTML(order.status)}</td>
                                <td class="actions">
                                    <a href="order_detail_view.php?id=${escapeHTML(order.order_id)}" class="btn btn-info btn-sm me-1">詳細</a>
                                    <a href="order_detail_edit.php?id=${escapeHTML(order.order_id)}&mode=edit" class="btn btn-warning btn-sm">編集</a>
                                </td>
                            `;
                            ordersTableBody.appendChild(row);
                        });
                    } else if (data.success && data.orders.length === 0) {
                        ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center">表示する注文がありません。</td></tr>';
                    } else {
                        ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">データの取得中にエラーが発生しました: ${escapeHTML(data.message || '不明なエラー')}</td></tr>`;
                    }
                } catch (error) {
                    console.error('Error fetching orders:', error);
                    ordersTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">データを取得できませんでした: ${escapeHTML(error.message)}</td></tr>`;
                }
            }

            // HTMLエスケープ関数
            function escapeHTML(str) {
                const div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }

            // 検索フォームの送信イベントリスナー
            searchForm.addEventListener('submit', function(event) {
                event.preventDefault(); // フォームのデフォルト送信を防止
                const orderId = document.getElementById('orderId').value;
                const customerName = document.getElementById('customerName').value;
                const paymentStatus = document.getElementById('paymentStatus').value;
                const deliveryStatus = document.getElementById('deliveryStatus').value;

                const params = {
                    orderId: orderId,
                    customerName: customerName,
                    paymentStatus: paymentStatus,
                    deliveryStatus: deliveryStatus
                };
                fetchOrders(params);
            });

            // リセットボタンのクリックイベントリスナー
            resetButton.addEventListener('click', function() {
                // フォームをリセット
                searchForm.reset();
                // フィルタリングなしで再度データを取得
                fetchOrders({});
            });

            // ページ読み込み時に初期データを取得
            fetchOrders();
        });
    </script>
    </body>
</html>