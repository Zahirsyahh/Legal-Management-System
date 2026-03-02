    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ config('app.name') }} - Notification</title>
        <style>
            /* Reset styles */
            body, p, h1, h2, h3, h4, h5, h6 {
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }
            
            body {
                background-color: #f5f5f5;
                line-height: 1.6;
                color: #333333;
                padding: 20px;
            }
            
            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }
            
            .email-header {
                background-color: #1e3c72;
                padding: 20px 30px;
                border-bottom: 3px solid #ff6b6b;
            }
            
            .email-header h1 {
                color: #ffffff;
                font-size: 20px;
                font-weight: 500;
                margin: 0;
            }
            
            .email-content {
                padding: 30px;
                background-color: #ffffff;
            }
            
            .greeting {
                font-size: 16px;
                color: #555555;
                margin-bottom: 20px;
            }
            
            .greeting strong {
                color: #333333;
            }
            
            .message-line {
                margin-bottom: 15px;
                color: #444444;
                font-size: 15px;
            }
            
            .detail-table {
                width: 100%;
                margin: 25px 0;
                border-collapse: collapse;
                background-color: #f8f9fa;
                border-radius: 6px;
                overflow: hidden;
            }
            
            .detail-table td {
                padding: 12px 15px;
                border-bottom: 1px solid #e9ecef;
                font-size: 14px;
            }
            
            .detail-table td:first-child {
                width: 120px;
                font-weight: 500;
                color: #666666;
                background-color: #f1f3f5;
            }
            
            .detail-table td:last-child {
                color: #333333;
                font-weight: 500;
            }
            
            .detail-table tr:last-child td {
                border-bottom: none;
            }
            
            .notes-box {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
            }
            
            .notes-box p {
                color: #856404;
                font-size: 14px;
                margin: 0;
                line-height: 1.5;
            }
            
            .notes-label {
                font-weight: 600;
                color: #856404;
                margin-bottom: 5px;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .action-button {
                text-align: center;
                margin: 30px 0 20px;
            }
            
            .action-button a {
                display: inline-block;
                background-color: #1e3c72;
                color: #ffffff;
                text-decoration: none;
                padding: 12px 30px;
                border-radius: 25px;
                font-size: 15px;
                font-weight: 500;
                transition: background-color 0.3s;
            }
            
            .action-button a:hover {
                background-color: #2a4a8a;
            }
            
            .email-footer {
                padding: 20px 30px;
                background-color: #f8f9fa;
                border-top: 1px solid #e9ecef;
            }
            
            .footer-text {
                color: #777777;
                font-size: 12px;
                line-height: 1.5;
                text-align: center;
            }
            
            .footer-text p {
                margin-bottom: 5px;
            }
            
            .footer-text .copyright {
                color: #999999;
                font-size: 11px;
                margin-top: 10px;
            }
            
            hr {
                border: none;
                border-top: 1px solid #e9ecef;
                margin: 15px 0;
            }
            
            @media only screen and (max-width: 480px) {
                .email-content {
                    padding: 20px;
                }
                
                .detail-table td:first-child {
                    width: 100px;
                }
                
                .email-header h1 {
                    font-size: 18px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <!-- Header without "Laravel" -->
            <div class="email-header">
                <h1>Legal Management System</h1>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                <!-- Greeting -->
                @isset($greeting)
                    <div class="greeting">
                        {!! $greeting !!}
                    </div>
                @endisset
                
                <!-- Intro Lines -->
                @foreach ($introLines as $line)
                    <div class="message-line">
                        {!! $line !!}
                    </div>
                @endforeach
                
                <!-- Special formatting for contract details -->
                @if(isset($contract) || isset($contractNumber) || isset($stage) || isset($fromStage) || isset($toStage) || isset($requestedBy))
                    <table class="detail-table">
                        @if(isset($contract) && isset($contract->title))
                        <tr>
                            <td>Contract</td>
                            <td>{{ $contract->title }}</td>
                        </tr>
                        @elseif(isset($contractTitle))
                        <tr>
                            <td>Contract</td>
                            <td>{{ $contractTitle }}</td>
                        </tr>
                        @endif
                        
                        @if(isset($contractNumber) && $contractNumber != 'N/A' && $contractNumber != '**')
                        <tr>
                            <td>Contract Number</td>
                            <td>{{ $contractNumber }}</td>
                        </tr>
                        @endif
                        
                        @if(isset($fromStage))
                        <tr>
                            <td>From Stage</td>
                            <td>{{ $fromStage }}</td>
                        </tr>
                        @endif
                        
                        @if(isset($toStage))
                        <tr>
                            <td>To Stage</td>
                            <td>{{ $toStage }}</td>
                        </tr>
                        @endif
                        
                        @if(isset($stage))
                        <tr>
                            <td>Stage</td>
                            <td>{{ $stage }}</td>
                        </tr>
                        @endif
                        
                        @if(isset($requestedBy))
                        <tr>
                            <td>Requested By</td>
                            <td>{{ $requestedBy }}</td>
                        </tr>
                        @elseif(isset($rejectedBy))
                        <tr>
                            <td>Rejected By</td>
                            <td>{{ $rejectedBy }}</td>
                        </tr>
                        @elseif(isset($assignedBy))
                        <tr>
                            <td>Assigned By</td>
                            <td>{{ $assignedBy }}</td>
                        </tr>
                        @endif
                        
                        @if(isset($dueDate))
                        <tr>
                            <td>Due Date</td>
                            <td>{{ $dueDate }}</td>
                        </tr>
                        @endif
                    </table>
                @endif
                
                <!-- Revision Notes specific -->
                @if(isset($revisionNotes))
                <div class="notes-box">
                    <div class="notes-label">Revision Notes</div>
                    <p>{{ $revisionNotes }}</p>
                </div>
                @endif
                
                <!-- Reason specific for rejection -->
                @if(isset($reason))
                <div class="notes-box" style="background-color: #f8d7da; border-left-color: #dc3545;">
                    <div class="notes-label" style="color: #721c24;">Reason</div>
                    <p style="color: #721c24;">{{ $reason }}</p>
                </div>
                @endif
                
                <!-- Action Button -->
                @isset($actionText)
                    <div class="action-button">
                        <a href="{{ $actionUrl }}" style="color: #ffffff;">{{ $actionText }}</a>
                    </div>
                @endisset
                
                <!-- Outro Lines -->
                @foreach ($outroLines as $line)
                    <div class="message-line" style="margin-top: 10px;">
                        {!! $line !!}
                    </div>
                @endforeach
                
                <!-- Additional info -->
                <div style="margin-top: 25px; padding-top: 15px; border-top: 1px dashed #e9ecef;">
                    <p style="color: #777777; font-size: 13px; margin-bottom: 5px;">
                        ⏱️ Time: {{ now()->format('d M Y H:i') }} WIB
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-text">
                    <p>This is an automated email from Legal Management System.</p>
                    <p>Please do not reply to this email.</p>
                    <div class="copyright">
                        <p>© {{ date('Y') }} Legal Management System. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>