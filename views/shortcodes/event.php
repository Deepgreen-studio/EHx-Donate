<div class="edp-ticket-area">
    <div class="edp-ticket-name-area">
        <p><?php esc_html_e('General Ticket', 'ehx-donate') ?></p>
        <p><?php echo esc_html(EHX_Donate_Helper::currencyFormat($ehx_event['ticket_price'])) ?></p>
    </div>
    <div class="edp-ticket-qty-area">
        <button type="button" class="edp-ticket-qty-btn">&minus;</button>
        <input type="text" class="edp-ticket-qty-input" value="1" autocomplete="off" readonly>
        <button type="button" class="edp-ticket-qty-btn">&plus;</button>
    </div>
    <div class="edp-ticket-btn-area">
        <button type="button" class="edp-ticket-btn"><?php esc_html_e('Reserve a spot', 'ehx-donate') ?></button>
    </div>
</div>


<div id="edp-callback-modal" class="edp-modal-window edp-modal-active edp-event-modal">
    <div class="edp-modal-dialog edp-modal-lg">
        <div class="edp-modal-content">
            <div class="edp-modal-header">

                <a href="#" title="Close" class="edp-modal-close">&#x2715;</a>
            </div>
            <div class="edp-modal-body">

                <div class="edp-body-content">
                    <div class="edp-body-left">

                        <div>
                            <h2>Contact information</h2>
                            <p>Log in for a faster experience.</p>
                        </div>

                    </div>
                    <div class="edp-body-right">
                        <div class="edp-body-right-content">
                            <div class="edp-body-right-thumbnail">
                                <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID)) ?>" alt="" srcset="">
                            </div>

                            <div class="edp-body-content-summary">

                                <div>
                                    <p class="edp-content-summary-title">Order summary</p>
                                    <p class="edp-content-summary-subtitle">Thursday, March 27 · 5:30 - 7:30pm GMT</p>
                                </div>


                                <div class="edp-content-summary-items">
                                    <div class="edp-content-summary-item">
                                        <div>1 x General Admission</div>
                                        <div>£0.00</div>
                                    </div>

                                    <div class="edp-content-summary-item">
                                        <div>
                                            Delivery
                                            <div>1 x eTicket</div>
                                        </div>
                                        <div>£0.00</div>
                                    </div>

                                </div>

                                <div class="edp-content-summary-total">
                                    <h4>Total</h4>
                                    <h4>0.00</h4>
                                </div>
                                
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>