
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Loan Journey Begins Now!</title>
</head>
<body style="margin:0; font-family:Arial, sans-serif; background:#f4f6f8; color:#333; padding:20px;">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
    <tr>
      <td style="padding:30px;">

        <h2 style="color:#0a9396; font-size:24px; margin-bottom:10px;">Your Loan is One Step Away 🚀</h2>
        <p style="font-size:16px;">Dear <strong><?= htmlspecialchars($empDetails->first_name) ?></strong>,</p>

        <p style="font-size:15px; line-height:1.6;">
          Great news! You've taken the first step toward getting instant financial support through <strong>Paisa On Salary</strong>. We've assigned one of our top executives to guide you through the final process.
        </p>

        <div style="background:#f1f5f9; border-left:4px solid #0a9396; padding:15px 20px; margin:20px 0; border-radius:6px;">
          <p style="margin:0;"><strong>👤 Executive Name:</strong> <?= htmlspecialchars($screener["name"]) ?></p>
          <p style="margin:0;"><strong>📞 Mobile:</strong> <a href="tel:<?= htmlspecialchars($screener["mobile"]) ?>" style="color:#0a9396;"><?= htmlspecialchars($screener["mobile"]) ?></a></p>
          <p style="margin:0;"><strong>💬 WhatsApp:</strong> <a href="https://wa.me/91<?= htmlspecialchars($screener["mobile"]) ?>" style="color:#0a9396;">Click to Chat</a></p>
        </div>

        <p style="font-size:15px;">
          ✅ Get up to <strong>₹90,000</strong><br/>
          ✅ Same-day processing<br/>
          ✅ No hassle, just basic documents<br/>
          ✅ Personal support till disbursal
        </p>

        <h3 style="margin-top:30px; font-size:18px; color:#333;">📄 Documents You’ll Need:</h3>
        <ul style="line-height:1.8; padding-left:20px;">
          <li>Aadhar Card (Front & Back)</li>
          <li>PAN Card (Original)</li>
          <li>Last 6 Months Bank Statement (PDF only)</li>
          <li>Last 3 Salary Slips / Certificate</li>
          <li>Proof of Address (Utility Bill and Rent Agreement)</li>
          <li>Official Email ID (if salary > ₹25,000)</li>
        </ul>

        <div style="margin:30px 0;">
          <a href="https://wa.me/91<?= htmlspecialchars($screener["mobile"]); ?>" style="background:#0a9396; color:#fff; padding:12px 20px; text-decoration:none; border-radius:5px; font-weight:bold;">
            💬 Connect on WhatsApp Now
          </a>
        </div>

        <p style="font-size:15px;">
          Let’s fast-track your loan! Our executive is ready to assist you. Don’t miss this opportunity to get the funds you need—on your terms.
        </p>

        <p style="margin-top:30px;">
          Warm regards,<br/>
          <strong>Team Paisa On Salary</strong><br/>
          📧 <a href="mailto:support@paisaonsalary.in" style="color:#0a9396;">support@paisaonsalary.in</a><br/>
          🌐 <a href="https://www.paisaonsalary.in" style="color:#0a9396;">www.paisaonsalary.com</a>
        </p>

      </td>
    </tr>
  </table>
</body>
</html>