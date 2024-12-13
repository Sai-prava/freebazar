@extends('frontend.dashboard.layouts.master')

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JavaScript Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<style>
    /* Default styles (light mode) */
    .modal-body {
        background-color: #fff;
        color: black;
    }

    .modal-body label,
    .modal-body strong {
        color: black;
    }

    /* Dark mode */
    @media (prefers-color-scheme: dark) {
        .modal-body {
            background-color: #333;
            color: white;
        }

        .modal-body label,
        .modal-body strong {
            color: white;
        }

        /* Input and select backgrounds */
        .modal-body .form-control {
            background-color: #444;
            color: white;
            border-color: #666;
        }

        .modal-body .form-control::placeholder {
            color: #bbb;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }
    }

    .modal-label {
        color: black;
        text-align: left;
        /* Ensure left alignment */
        display: block;
        /* Make sure it aligns left as a block element */
    }
</style>
@section('content')
    <div style="margin-top: 20px;">
        <h4><b>HI ,{{ auth()->user()->name }} <span>Welcome to Dashboard</span></b></h4>
        <hr>
    </div>


    <div class="row" style="margin-top: 20px;">
        <div class="col-12" style="margin-top: 15px;">
            <div class="card illustration flex-fill">
                <div class="card-body p-0 d-flex flex-fill">
                    <div class="row g-0 w-100">
                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <span>
                                <img style="height: 50px; width: 50px; margin-left: 20px; margin-top: 20px;"
                                    src="{{ asset('images/' . auth()->user()->image) }}" alt=""
                                    class="thumb-lg rounded-circle">
                            </span>
                            <h4 class="illustration-text" style="color: #062962; margin-left: 15px;">
                                <b>{{ auth()->user()->name }} ({{ auth()->user()->user_id }})</b>
                                <img src="{{ asset('images/admin/customer-support.png') }}" alt="Customer Support"
                                    class="img-fluid illustration-img" style="margin-left: 10px;">
                            </h4>
                        </div>
                        <div class="col-12 col-md-6 align-self-end text-center text-md-end">
                            <button id="my-qr-reader" class="btn btn-warning mb-2">Scan Payment QR Code</button>

                            <!-- QR Code Scanner Modal -->
                            <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="qrScannerModalLabel">Scan QR Code</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="qr-reader"
                                                style="width: 100%; height: 300px; background-color: rgb(238, 179, 92);">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QR Code Details Modal -->
                            <div class="modal fade" id="qrDetailsModal" tabindex="-1" aria-labelledby="qrDetailsModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="qrDetailsModalLabel">QR Code Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h5 id="qr-details-text"></h5>
                                            {{-- <input type="text" id="qrData" name="pos_id"> --}}
                                            <button id="openBillingModal" class="btn btn-primary">OK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Modal -->
                            <div class="modal fade" id="billingModal" tabindex="-1" aria-labelledby="billingModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="billingModalLabel" style="color: black">Billing
                                                Information</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="qrForm" method="post" action="{{ route('user.payment') }}">
                                                @csrf
                                                <input type="hidden" name="pos_id" id="qrDataId">
                                                <input type="hidden" name="invoice">
                                                <input type="hidden" name="user_id" value="{{ $user_profile->id }}">
                                                <input type="hidden" name="mobilenumber"
                                                    value="{{ $user_profile->mobilenumber }}">
                                                <input type="hidden" name="insert_date" id="insert_date">
                                                <input type="hidden" name="transaction_date" id="transaction_date"
                                                    value="{{ now()->format('Y-m-d') }}">
                                                <div class="form-group">
                                                    <label for="billing_amount" style="color: black;"
                                                        class="modal-label">Billing Amount</label>
                                                    <input type="number" class="form-control" id="billing_amount"
                                                        name="billing_amount" required min="0" step="any"
                                                        oninput="checkWalletBalance()">
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label for="pay_by" style="color: black;" class="modal-label">Pay
                                                        By</label>
                                                    <select class="form-control" id="pay_by" name="pay_by" required>
                                                        <option value="wallet">Wallet</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="upi">UPI</option>
                                                    </select>
                                                </div>

                                                <div class="form-group mt-2">
                                                    <label for="paying_amount"
                                                        style="color: black;margin-right: 367px;">Paying Amount</label>
                                                    <input name="paying_amount" id="paying_amount" type="number"
                                                        class="form-control" required min="0">
                                                </div>

                                                <div class="wallet-balance mt-2">
                                                    <strong style="color: black;">Your Wallet Balance: </strong>
                                                    <span id="wallet_balance">{{ $walletBalance }}</span>
                                                    <input type="hidden" name="wallet_balance"
                                                        value="{{ $walletBalance }}">
                                                </div>

                                                <div class="insufficient-balance mt-2" style="display: none;">
                                                    <strong class="modal-label" style="color: black;">Wallet balance is
                                                        insufficient. Please
                                                        choose an alternative payment method for the remaining
                                                        amount:</strong>
                                                    <select name="alternative_pay_by" id="alternative_pay_by"
                                                        class="form-control">
                                                        <option value="cash">Cash</option>
                                                        <option value="upi">UPI</option>
                                                    </select>
                                                </div>

                                                <div class="remaining-balance mt-2" style="display: none;">
                                                    <label for="remaining_amount" class="modal-label"
                                                        style="color: black;">Remaining Amount
                                                        to be Paid:</label>
                                                    <input type="text" id="remaining_amount" class="form-control"
                                                        readonly>
                                                </div>
                                                <button type="submit" class="btn btn-success mt-2">Submit
                                                    Payment</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="result"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <!-- Personal Information Card -->
                <div class="col-md-3 col-12 mb-4">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2"><b>Personal Information</b></h5>
                                    <div class="mt-4">
                                        <p class="mb-1">Full Name: {{ auth()->user()->name }}</p>
                                        <p class="mb-1">Email: {{ auth()->user()->email }}</p>
                                        <p class="mb-1">
                                            Your Sponsor:
                                            @php
                                                $sponsor = auth()->user()->sponsor_id
                                                    ? \App\Models\User::find(auth()->user()->sponsor_id)
                                                    : null;
                                            @endphp
                                            {{ $sponsor->user_id ?? 'N/A' }}
                                        </p>
                                        <p class="mb-1">Mobile Number: {{ auth()->user()->mobilenumber ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('frontend.index') }}" class="btn btn-info btn-sm">Shop Now</a>
                                    </div>
                                </div>
                                <div class="d-inline-block ms-3">
                                    <div class="stat">
                                        <svg style="width: 35px; height: 35px;" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            class="feather feather-user align-middle text-primary">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wallet Balance Card -->
                <div class="col-md-3 col-6 mb-4">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h3 class="mb-2"><b style="color: red"><i class="fa fa-inr"
                                                aria-hidden="true"></i> {{ $walletBalance }}/-</b></h3>
                                    <p class="mb-2">Wallet Balance</p>
                                </div>
                                <div class="d-inline-block ms-3">
                                    <div class="stat">
                                        <svg style="width: 35px; height: 35px;" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            class="feather feather-credit-card align-middle text-primary">
                                            <rect x="1" y="4" width="22" height="16" rx="2"
                                                ry="2"></rect>
                                            <line x1="1" y1="10" x2="23" y2="10"></line>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Purchase Card -->
                <div class="col-md-3 col-6 mb-4">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h3 class="mb-2"><b style="color: red"><i class="fa fa-inr"
                                                aria-hidden="true"></i> {{ $monthlyPurchase }}/-</b></h3>
                                    <p class="mb-2">Monthly Purchase</p>
                                </div>
                                <div class="d-inline-block ms-3">
                                    <div class="stat">
                                        <svg style="width: 35px; height: 35px;" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            class="feather feather-shopping-cart align-middle text-primary">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path
                                                d="M2 2h3l3.6 7.59 1.29-2.59H16l1.5 3H21a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V9.42L5.4 4.41 4 2H2">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payback Achieved Card -->
                <div class="col-md-3 col-6 mb-4">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h3 class="mb-2">{{ $count['posts'] ?? 0 }}</h3>
                                    <p class="mb-2">Payback Achieved</p>
                                </div>
                                <div class="d-inline-block ms-3">
                                    <div class="stat">
                                        <svg style="width: 35px; height: 35px;" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            class="feather feather-dollar-sign align-middle text-primary">
                                            <path
                                                d="M12 1v4m0 14v4m-5-5h10m-8 0h1m-1-7h3m1 0h1m0-4h1m1 0h1m0 4h1m1 0h1m0 4h1m-7 0v3m5-3v3m-4-9h4">
                                            </path>
                                            <path d="M13.6 15.6l1.8-1.8 3.2 3.2-1.8 1.8-3.2-3.2"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sponsored Users Card -->
                <div class="col-md-3 col-12 mb-4">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="flex-grow-1">
                                <h5 class="mb-2"
                                    style="background-color: #4fc9da; color: #062962; font-size: 16px; text-align: center; padding: 10px 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); font-weight: 600;">
                                    Your Sponsored Users
                                </h5>
                                @foreach ($sponsors as $data)
                                    <div class="d-flex align-items-center">
                                        <span>
                                            <img style="height: 50px; width: 50px;"
                                                src="https://freebazar.in/assets/treeview/images/blank-yel.jpg"
                                                alt="" class="thumb-lg rounded-circle">
                                        </span>
                                        <div style="margin-left: 10px;">
                                            <p class="mb-1">
                                                @if ($data->user)
                                                    {{ $data->user->name }}
                                                    ({{ $data->user->user_id }})
                                                @endif

                                            </p>
                                        </div>
                                    </div>
                                    <hr>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POS Transactions Table -->
                <div class="col-md-6 col-12 mb-4">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <h6><b>POS Transaction (Latest 5 Transactions)</b></h6>

                            <!-- Date Filter Form -->
                            <form action="{{ route('user.index') }}" method="GET" class="form-inline mb-3">
                                <div class="input-group mb-3">
                                    <div class="me-2">
                                        <label for="from_date" class="form-label">From</label>
                                        <input type="date" class="form-control" name="from_date" id="from_date"
                                            value="{{ request('from_date') }}" placeholder="From Date">
                                    </div>

                                    <div class="me-2">
                                        <label for="to_date" class="form-label">To</label>
                                        <input type="date" class="form-control" name="to_date" id="to_date"
                                            value="{{ request('to_date') }}" placeholder="To Date">
                                    </div>

                                    <button class="btn btn-info btn-sm" type="submit">Search Transactions</button>
                                </div>
                            </form>


                            <!-- Responsive Table -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sl.No</th>
                                            <th>POS Name</th>
                                            <th>Transaction Date</th>
                                            <th>Amount</th>
                                            <th>Transaction Mode</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($walletList->take(5) as $key => $data)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $data->getPos->name ?? 'N/A' }}</td>
                                                <td>{{ date('d/m/Y', strtotime($data->transaction_date)) }}</td>
                                                <td>₹{{ $data->amount ?? 0 }}/-</td>
                                                <td>{{ $data->pay_by }}</td>
                                                <td>
                                                    @if ($data->status == 0)
                                                        <span class="btn btn-danger btn-sm">Unverified</span>
                                                    @else
                                                        <span class="btn btn-success btn-sm">Verified</span>
                                                    @endif
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    <div class="alert alert-danger" role="alert">
                                                        No transaction record found.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>

    </div>
    <script src="https://unpkg.com/html5-qrcode"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> --}}

    <script>
        // function domReady(fn) {
        //     if (
        //         document.readyState === "complete" ||
        //         document.readyState === "interactive"
        //     ) {
        //         setTimeout(fn, 1000);
        //     } else {
        //         document.addEventListener("DOMContentLoaded", fn);
        //     }
        // }

        // domReady(function() {
        //     // If found you qr code
        //     function onScanSuccess(decodeText, decodeResult) {
        //         alert("You Qr is : " + decodeText, decodeResult);
        //     }

        //     let htmlscanner = new Html5QrcodeScanner("my-qr-reader", {
        //         fps: 10,
        //         qrbos: 250,
        //     });
        //     htmlscanner.render(onScanSuccess);
        // });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Reference to scanner instance for global access
            let htmlscanner;

            // Open the QR Code Scanner Modal when the scan button is clicked
            document.getElementById("my-qr-reader").addEventListener("click", function() {
                let qrModal = new bootstrap.Modal(document.getElementById("qrScannerModal"), {
                    backdrop: 'static',
                    keyboard: false
                });
                qrModal.show();
                startMainScanner();
            });

            // QR Code Scanner function
            function startMainScanner() {
                // Initialize a new Html5QrcodeScanner instance
                htmlscanner = new Html5QrcodeScanner("qr-reader", {
                    fps: 10,
                    qrbox: 250
                });

                // Render the scanner and handle successful scan
                htmlscanner.render((decodedText, decodedResult) => {
                    // Hide QR Scanner modal on successful scan
                    let qrModal = bootstrap.Modal.getInstance(document.getElementById("qrScannerModal"));
                    qrModal.hide();

                    let parts = decodedText.split('|');
                    let name = parts[0]; // The name part before '|'
                    let id = parts[1]; // The id part after '|'
                    document.getElementById("qr-details-text").innerHTML = "POS NAME: <b>" + name + "</b>";

                    // Store the ID in a hidden input field
                    document.getElementById("qrDataId").value = id;
                    console.log("POS ID: " + id);

                    // Open the QR Details modal and stop the scanner
                    let qrDetailsModal = new bootstrap.Modal(document.getElementById("qrDetailsModal"), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    htmlscanner.clear(); // Properly stop the scanner
                    qrDetailsModal.show();
                });
            }

            // Stop the scanner if the QR scanner modal is manually closed
            document.getElementById("qrScannerModal").addEventListener('hidden.bs.modal', function() {
                if (htmlscanner) {
                    htmlscanner.clear(); // Ensure scanner is stopped
                }
            });

            // Handle OK button click in the QR Details modal to open Billing Modal
            document.getElementById("openBillingModal").addEventListener("click", function() {
                let qrDetailsModal = bootstrap.Modal.getInstance(document.getElementById("qrDetailsModal"));
                qrDetailsModal.hide();

                let billingModal = new bootstrap.Modal(document.getElementById("billingModal"), {
                    backdrop: 'static',
                    keyboard: false
                });
                billingModal.show();
            });
        });
    </script>
    <script>
        let isDeducted = false; // Flag to ensure deduction happens only once
        let originalWalletBalance = 0; // Store the original wallet balance

        function checkWalletBalance() {
            const billingAmount = parseFloat(document.getElementById('billing_amount').value) || 0;
            const walletBalanceElement = document.getElementById('wallet_balance');
            let walletBalance = parseFloat(walletBalanceElement.textContent) || 0;

            // Save the original wallet balance when the function runs for the first time
            if (originalWalletBalance === 0) {
                originalWalletBalance = walletBalance;
            }

            const payBySelect = document.getElementById('pay_by');
            const payingAmountField = document.getElementById('paying_amount');
            const insufficientBalanceDiv = document.querySelector('.insufficient-balance');
            const alternativePayBySelect = document.getElementById('alternative_pay_by');
            const remainingAmountField = document.getElementById('remaining_amount');

            if (payBySelect.value === "wallet") {
                // Reset wallet balance to the original amount
                walletBalanceElement.textContent = originalWalletBalance.toFixed(2);

                // Show the full billing amount
                payingAmountField.value = billingAmount.toFixed(2);
                insufficientBalanceDiv.style.display = 'none';
                alternativePayBySelect.required = false;
                remainingAmountField.style.display = 'none';

                // Reset the deduction flag
                isDeducted = false;

            } else if (payBySelect.value === "cash" || payBySelect.value === "upi") {
                const walletDeduction = billingAmount * 0.05; // Calculate 5% charge

                if (!isDeducted) {
                    let deductedFromWallet = 0;

                    if (walletBalance >= walletDeduction) {
                        // Wallet balance is sufficient for 5% deduction
                        deductedFromWallet = walletDeduction;
                        walletBalance -= walletDeduction;
                    } else {
                        // Wallet balance is insufficient, deduct whatever is available
                        deductedFromWallet = walletBalance;
                        walletBalance = 0; // Wallet is now empty
                    }

                    // Update wallet balance
                    walletBalanceElement.textContent = walletBalance.toFixed(2);

                    // Calculate payable amount by subtracting the actual deduction
                    const updatedPayableAmount = (billingAmount - deductedFromWallet).toFixed(2);
                    payingAmountField.value = updatedPayableAmount;

                    // Show remaining balance if necessary
                    remainingAmountField.value = updatedPayableAmount;
                    remainingAmountField.style.display = 'block';

                    isDeducted = true;
                }

                insufficientBalanceDiv.style.display = 'none';
                alternativePayBySelect.required = false;

            } else {
                // For other payment methods, show full billing amount
                payingAmountField.value = billingAmount.toFixed(2);
                insufficientBalanceDiv.style.display = 'none';
                alternativePayBySelect.required = false;

                // Reset the deduction flag
                isDeducted = false;
            }
        }

        // Event listeners for real-time updates
        document.getElementById('pay_by').addEventListener('change', checkWalletBalance);
        document.getElementById('billing_amount').addEventListener('input', checkWalletBalance);
    </script>
@endsection
