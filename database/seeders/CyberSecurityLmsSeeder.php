<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CyberSecurityLmsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        // 1. Course: Phishing & Social Engineering Defense
        $course1 = Course::updateOrCreate(
            ['slug' => 'phishing-and-social-engineering-defense'],
            [
                'title' => 'Phishing & Social Engineering Defense',
                'category' => 'Email & Social Engineering',
                'level' => 'beginner',
                'summary' => 'Master the tactics cybercriminals use to trick employees, manipulate human psychology, and steal confidential credentials.',
                'description' => 'Social engineering accounts for over 85% of initial cyber intrusions in corporate and personal environments. This comprehensive course prepares you to recognize psychological manipulation, deconstruct suspicious email headers, detect deceptive links, and respond effectively to targeted spear-phishing attacks.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
                'estimated_minutes' => 30,
                'badge_name' => 'Phishing Defender',
                'order' => 1,
                'is_published' => true,
            ]
        );

        $module1_1 = CourseModule::updateOrCreate(
            ['course_id' => $course1->id, 'title' => 'Module 1: The Psychology of Deception'],
            [
                'description' => 'Understand how attackers leverage urgency, fear, authority, and greed to bypass critical thinking.',
                'order' => 1,
            ]
        );

        $lesson1_1_1 = Lesson::updateOrCreate(
            ['course_module_id' => $module1_1->id, 'slug' => 'anatomy-of-modern-phishing-attacks'],
            [
                'title' => 'Anatomy of Modern Phishing Attacks',
                'type' => 'video',
                'duration_minutes' => 8,
                'video_url' => 'https://www.youtube.com/embed/XBkzBrXllBo',
                'threat_level' => 'high',
                'order' => 1,
                'is_published' => true,
                'key_takeaways' => [
                    'Attackers manufacture artificial urgency ("Account suspended in 24 hours") to force hasty mistakes.',
                    'Display name spoofing tricks users who only check the sender name rather than the full email header.',
                    'Spear-phishing uses OSINT gathered from LinkedIn and corporate directories to appear authentic.',
                ],
                'content' => "### Understanding Modern Phishing Vectors

Phishing is no longer just broken English emails promising lottery fortunes. Today's cyber syndicates deploy targeted spear-phishing campaigns that meticulously mimic your cloud service providers, financial institutions, and internal management.

#### The 4 Psychological Triggers Attackers Exploit
1. **Urgency & Panic**: Threatening account closure, disciplinary action, or immediate legal penalties if you don't act within minutes.
2. **Authority & Hierarchical Pressure**: Impersonating executive leadership (CEO fraud, CFO gift-card or wire transfer scams).
3. **Curiosity & Greed**: Promising bonuses, leaked payroll documents, or exclusive unreleased benefits.
4. **Familiarity & Sympathy**: Posing as a vendor or coworker experiencing a crisis requiring immediate invoice rerouting.

> ⚠️ **CyberShield Security Rule**: Whenever an incoming message commands urgent action regarding credentials, payments, or sensitive attachments, immediately halt and verify via an independent out-of-band channel (such as a direct phone call or Slack message).",
            ]
        );

        $lesson1_1_2 = Lesson::updateOrCreate(
            ['course_module_id' => $module1_1->id, 'slug' => 'spotting-deceptive-urls-and-quishing'],
            [
                'title' => 'Spotting Deceptive URLs & QR Phishing (Quishing)',
                'type' => 'article',
                'duration_minutes' => 10,
                'threat_level' => 'critical',
                'order' => 2,
                'is_published' => true,
                'key_takeaways' => [
                    'Always read URLs from right to left before the first single slash to find the true registered domain.',
                    'QR codes (Quishing) bypass email gateway URL filters and blind traditional scanners.',
                    'Homograph attacks substitute lookalike Cyrillic characters (e.g., "а" vs "a") into genuine-looking domain names.',
                ],
                'content' => "### Deconstructing Deceptive Web Addresses

Attackers rely on subtle visual mimicry to disguise malicious landing pages as legitimate login portals. Understanding domain anatomy is your first line of defense.

#### The Hierarchy of a URL: Finding the True Host
Consider this deceptive URL:
`https://accounts.google.com.security-verify-token.net/login`

- Subdomain: `accounts.google.com` (attacker-controlled text meant to deceive you)
- True Root Domain: `security-verify-token.net` (this is the actual server you are communicating with!)
- Top Level Domain (TLD): `.net`

```text
[https://]  [accounts.google.com.]  [security-verify-token]  [.net]  [/login]
  Scheme         Subdomain                Domain Name         TLD     Path
               (DECEPTION)            (ACTUAL DESTINATION)
```

#### Beware of QR Code Phishing ('Quishing')
Attackers increasingly embed QR codes in PDF attachments or emails under the guise of 'Two-Factor Authentication Setup' or 'Mandatory Security Update'. Because email security appliances cannot always decode image contents, the QR code reaches your inbox. Scanning it with your personal phone moves you onto an unmonitored mobile network without endpoint protection.

#### Critical Checklist Before Entering Credentials:
- [ ] Did you independently type the URL into your browser bookmark?
- [ ] Does the URL bar show the verified domain without extraneous hyphenated words?
- [ ] Is your password manager offering auto-fill? If a password manager does not offer to auto-fill your saved credentials, it usually means the domain does not match!",
            ]
        );

        $lesson1_1_3 = Lesson::updateOrCreate(
            ['course_module_id' => $module1_1->id, 'slug' => 'phishing-scenario-challenge'],
            [
                'title' => 'Interactive Phishing Scenario Challenge',
                'type' => 'interactive_quiz',
                'duration_minutes' => 12,
                'threat_level' => 'critical',
                'order' => 3,
                'is_published' => true,
                'key_takeaways' => [
                    'Analyze real-world scenarios carefully before choosing your response.',
                    'Report all suspected phishing emails directly to your CyberShield security operations center.',
                    'A 100% score unlocks the Phishing Defender badge!',
                ],
                'content' => "### Test Your Phishing Detection Skills

Put your investigative skills to the test with these realistic corporate scenarios. Analyze the email sender, context, and requested action to choose the safest protocol.",
            ]
        );

        // Quiz questions for lesson 1_1_3
        QuizQuestion::updateOrCreate(
            ['lesson_id' => $lesson1_1_3->id, 'order' => 1],
            [
                'scenario' => 'You receive an urgent email from "IT Helpdesk <support-desk@internal-corp-update.com>" stating: "Your Microsoft 365 password expires in 45 minutes. Click here to maintain current access."',
                'question' => 'What is the most critical red flag in this message?',
                'options' => [
                    'The email is written in plain text rather than HTML.',
                    'The domain "internal-corp-update.com" is an external, unverified domain, and IT never creates arbitrary 45-minute deadlines.',
                    'The email does not have a logo attached.',
                    'The email arrived in the morning rather than afternoon.',
                ],
                'correct_option_index' => 1,
                'explanation' => 'Attackers register lookalike domains like "internal-corp-update.com" to impersonate internal IT staff. Real password policies do not expire within 45 minutes without prior weeks of notice.',
            ]
        );

        QuizQuestion::updateOrCreate(
            ['lesson_id' => $lesson1_1_3->id, 'order' => 2],
            [
                'scenario' => 'An email appears to be from your company CFO asking you to purchase ten $100 Apple gift cards for a top client surprise and email the scratch-off PIN codes right away because they are stuck in a board meeting.',
                'question' => 'What is this classic attack called and how should you respond?',
                'options' => [
                    'It is a Ransomware attack; delete your hard drive.',
                    'It is CEO Fraud / Business Email Compromise (BEC); do not purchase the cards and verify with the executive through a separate verified channel.',
                    'It is legitimate corporate gift procurement; buy the cards immediately.',
                    'Forward the email to all colleagues to ask if anyone else got one.',
                ],
                'correct_option_index' => 1,
                'explanation' => 'Business Email Compromise (BEC) and gift-card scams frequently use the "busy executive in a meeting" pretext because gift card numbers are untraceable once redeemed.',
            ]
        );

        QuizQuestion::updateOrCreate(
            ['lesson_id' => $lesson1_1_3->id, 'order' => 3],
            [
                'scenario' => 'You receive a calendar invite from an unfamiliar external user with an attachment titled "Meeting_Agenda_Q3.pdf.exe" or a link to a OneDrive shared document.',
                'question' => 'Why is this attachment especially dangerous?',
                'options' => [
                    'PDF files cannot be opened in calendar applications.',
                    'It uses a double extension (.pdf.exe) to trick Windows users into launching an executable program instead of a document.',
                    'Calendar invites do not support attachments.',
                    'Executable files are always safe if scanned by default antivirus.',
                ],
                'correct_option_index' => 1,
                'explanation' => 'Double extension tricks exploit default OS settings that hide file extensions, causing users to believe they are opening a PDF when they are actually running malware.',
            ]
        );

        // 2. Course: Ransomware & Endpoint Defense
        $course2 = Course::updateOrCreate(
            ['slug' => 'ransomware-and-endpoint-threat-defense'],
            [
                'title' => 'Ransomware & Endpoint Threat Defense',
                'category' => 'Malware & Threat Defense',
                'level' => 'intermediate',
                'summary' => 'Understand ransomware propagation, weaponized office macros, drive-by zero-days, and rapid containment protocols.',
                'description' => 'Ransomware has crippled hospitals, infrastructure, and multinational enterprises. In this course, you will learn the exact kill chain used by modern ransomware operators—from initial delivery and defense evasion to privilege escalation and data exfiltration—and how to isolate contaminated endpoints before network encryption begins.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80',
                'estimated_minutes' => 40,
                'badge_name' => 'Endpoint Sentinel',
                'order' => 2,
                'is_published' => true,
            ]
        );

        $module2_1 = CourseModule::updateOrCreate(
            ['course_id' => $course2->id, 'title' => 'Module 1: The Ransomware Kill Chain'],
            [
                'description' => 'Deconstruct each stage of a ransomware deployment from initial compromise to hostage extortion.',
                'order' => 1,
            ]
        );

        $lesson2_1_1 = Lesson::updateOrCreate(
            ['course_module_id' => $module2_1->id, 'slug' => 'how-ransomware-infiltrates-workstations'],
            [
                'title' => 'How Ransomware Infiltrates Workstations',
                'type' => 'video',
                'duration_minutes' => 9,
                'video_url' => 'https://www.youtube.com/embed/5m0rA_qEab0',
                'threat_level' => 'critical',
                'order' => 1,
                'is_published' => true,
                'key_takeaways' => [
                    'Double extortion means attackers steal your proprietary data before encrypting your local files.',
                    'Living-off-the-land binaries (LOLBins) like PowerShell and WMI are used to evade traditional file-scanning antivirus.',
                    'Disconnecting network cables immediately arrests lateral movement during active encryption.',
                ],
                'content' => "### The Evolution of Modern Ransomware

Gone are the days when ransomware was a simple automated virus that immediately encrypted your C: drive upon download. Modern Ransomware-as-a-Service (RaaS) operations act like stealthy penetration testers:

1. **Initial Access**: Gained through unpatched VPN vulnerabilities, phishing with malware loaders, or stolen remote desktop credentials.
2. **Reconnaissance & Persistence**: Attackers stay dormant for an average of 14 days, surveying backups and active directory domain controllers.
3. **Exfiltration (Double Extortion)**: Encrypting backups is not enough; attackers exfiltrate terabytes of proprietary customer records and threaten public leaks if the ransom isn't paid.
4. **Execution**: Ransomware binary is detonated on nights or holiday weekends when IT response times are lowest.",
            ]
        );

        $lesson2_1_2 = Lesson::updateOrCreate(
            ['course_module_id' => $module2_1->id, 'slug' => 'usb-hygiene-and-removable-media-hazards'],
            [
                'title' => 'USB Hygiene & Removable Media Hazards',
                'type' => 'article',
                'duration_minutes' => 11,
                'threat_level' => 'high',
                'order' => 2,
                'is_published' => true,
                'key_takeaways' => [
                    'Never insert unknown USB drives found in parking lots or lobbies (USB Drop Attacks).',
                    'BadUSB devices (like Rubber Ducky) disguise themselves as standard keyboards to inject high-speed keystroke payloads.',
                    'Hardware write-blockers and dedicated air-gapped quarantine stations protect production systems.',
                ],
                'content' => "### Physical Vectors: The Dangers of Removable Media

Even the most hardened perimeter firewalls can be rendered completely useless by an employee plugging an infected USB drive directly into a desktop behind the firewall.

#### The USB Drop Attack (Social Engineering)
Attackers frequently drop branded flash drives labeled 'Q4 Executive Salaries' or 'Confidential Staff Layoffs' in parking lots, restrooms, or elevator banks. Over 48% of people who find a discarded USB drive plug it into a computer out of curiosity.

#### What Happens When You Plug It In?
- **BadUSB Microcontrollers**: The device doesn't register as mass storage; it identifies as a Human Interface Device (HID keyboard). Your computer trusts keyboards automatically and permits it to type 1,000 words per minute, launching command prompts and fetching remote payloads.
- **Autorun Exploits**: While modern operating systems disable automatic execution by default, shortcuts with icon overlays trick users into clicking what appears to be an innocent folder.",
            ]
        );

        $lesson2_1_3 = Lesson::updateOrCreate(
            ['course_module_id' => $module2_1->id, 'slug' => 'incident-response-and-containment-protocol'],
            [
                'title' => 'Incident Response & Containment Protocol',
                'type' => 'interactive_quiz',
                'duration_minutes' => 10,
                'threat_level' => 'critical',
                'order' => 3,
                'is_published' => true,
                'key_takeaways' => [
                    'Action within the first 5 minutes determines the scope of data loss.',
                    'Physically disconnect network connectivity immediately without shutting down the computer power to preserve RAM forensics.',
                ],
                'content' => "### Live Incident Response Drill

When an outbreak begins, panic causes mistakes. Test your immediate containment instincts under high pressure.",
            ]
        );

        QuizQuestion::updateOrCreate(
            ['lesson_id' => $lesson2_1_3->id, 'order' => 1],
            [
                'scenario' => 'You observe your desktop files suddenly being renamed with a strange extension like ".locked" or ".crypt", and your fan begins spinning at maximum speed.',
                'question' => 'What is the absolute first action you should take?',
                'options' => [
                    'Power off the computer and throw it in the trash.',
                    'Disconnect the Ethernet cable and disable Wi-Fi immediately to cut network communication.',
                    'Email your manager to ask what they think is happening.',
                    'Try opening the files repeatedly to see if they fix themselves.',
                ],
                'correct_option_index' => 1,
                'explanation' => 'Unplugging Ethernet and killing Wi-Fi prevents the malware from communicating with command-and-control servers to obtain encryption keys, and stops it from spreading to shared network drives and coworkers.',
            ]
        );

        QuizQuestion::updateOrCreate(
            ['lesson_id' => $lesson2_1_3->id, 'order' => 2],
            [
                'scenario' => 'A ransomware ransom note appears on screen demanding 2 Bitcoin within 48 hours to decrypt your corporate files.',
                'question' => 'Should your company immediately pay the ransom without consulting law enforcement or incident responders?',
                'options' => [
                    'Yes, criminals always honor their promises and will restore your files.',
                    'No; paying encourages future attacks, there is no guarantee of data recovery, and paying sanctioned cyber syndicates may violate federal law.',
                    'Yes, but only if you negotiate the ransom down by 50%.',
                    'No, you should post the ransom note publicly on Twitter/X first.',
                ],
                'correct_option_index' => 1,
                'explanation' => 'Law enforcement and cybersecurity agencies advise against paying ransoms. Research shows that over 80% of organizations that pay are targeted a second time, and many never receive functional decryption tools.',
            ]
        );

        // 3. Course: Password Hygiene & Passkeys
        $course3 = Course::updateOrCreate(
            ['slug' => 'passwords-passkeys-and-identity-security'],
            [
                'title' => 'Passwords, Passkeys & Identity Security',
                'category' => 'Identity & Authentication',
                'level' => 'beginner',
                'summary' => 'Ditch fragile passwords. Explore passkeys, hardware security keys (FIDO2), authenticator apps, and password vaults.',
                'description' => 'Password reuse is responsible for billions of compromised accounts worldwide. In this course, explore how modern authentication is transitioning away from memorized strings toward cryptographic passkeys, why SMS 2FA is vulnerable to SIM swapping, and how enterprise password managers safeguard corporate identity.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?auto=format&fit=crop&w=800&q=80',
                'estimated_minutes' => 25,
                'badge_name' => 'Cryptographic Guardian',
                'order' => 3,
                'is_published' => true,
            ]
        );

        $module3_1 = CourseModule::updateOrCreate(
            ['course_id' => $course3->id, 'title' => 'Module 1: Authentication Architecture'],
            [
                'description' => 'From passwords and brute force to WebAuthn and cryptographic keys.',
                'order' => 1,
            ]
        );

        $lesson3_1_1 = Lesson::updateOrCreate(
            ['course_module_id' => $module3_1->id, 'slug' => 'why-passwords-are-obsolete'],
            [
                'title' => 'Why Traditional Passwords Fail',
                'type' => 'article',
                'duration_minutes' => 7,
                'threat_level' => 'medium',
                'order' => 1,
                'is_published' => true,
                'key_takeaways' => [
                    'Length beats complexity: A 16-character passphrase is orders of magnitude stronger than an 8-character complex string.',
                    'Credential stuffing tests billions of leaked password combinations automatically against thousands of web services.',
                ],
                'content' => "### The Flaw of Memorized Secrets

Human memory is inherently unsuited for high-entropy cryptography. When forced to change passwords every 90 days, people resort to predictable substitutions (`P@ssw0rd1! -> P@ssw0rd2!`), which automated cracking tools anticipate in milliseconds.

#### How Attackers Breach Passwords:
- **Credential Stuffing**: Attackers take usernames and passwords from old leaks (like LinkedIn or Adobe) and use automated bots to try them against your bank, email, and internal work portals.
- **Brute Force & Hashcat**: Modern GPU clusters can compute billions of NTLM or MD5 hashes per second.
- **Keyloggers & Info-Stealers**: Malware installed on compromised home PCs silently dumps browser-saved passwords.",
            ]
        );

        $lesson3_1_2 = Lesson::updateOrCreate(
            ['course_module_id' => $module3_1->id, 'slug' => 'fido2-and-passkeys-explained'],
            [
                'title' => 'FIDO2 & The Passkey Revolution',
                'type' => 'video',
                'duration_minutes' => 10,
                'video_url' => 'https://www.youtube.com/embed/k4Jp72W0uX8',
                'threat_level' => 'low',
                'order' => 2,
                'is_published' => true,
                'key_takeaways' => [
                    'Passkeys are mathematically unphishable because your browser verifies the domain origin before signing.',
                    'The private key never leaves your local device (secure enclave or hardware token).',
                    'No shared secret is stored on the server that could be stolen in a database breach.',
                ],
                'content' => "### How Passkeys Eliminate Phishing Entirely

Passkeys use public-key cryptography (WebAuthn / FIDO2). When you register a passkey:
1. Your device creates a unique key pair for that specific domain.
2. The public key is sent to the server.
3. The private key remains encrypted inside your phone, laptop secure enclave, or YubiKey.

Even if you click a fake phishing URL, your browser checks the actual domain name. Because the phishing domain does not match the registered domain, your device will simply refuse to sign the authentication challenge!",
            ]
        );

        // 4. Course: Safe Remote Work & Public Wi-Fi Defense
        $course4 = Course::updateOrCreate(
            ['slug' => 'safe-remote-work-and-public-wifi-defense'],
            [
                'title' => 'Safe Remote Work & Public Wi-Fi Defense',
                'category' => 'Network & Mobile Security',
                'level' => 'intermediate',
                'summary' => 'Defend yourself when working from coffee shops, airports, and hotels against Evil Twin APs and Man-in-the-Middle attacks.',
                'description' => 'Remote work affords incredible flexibility, but untrusted network environments introduce unique attack vectors. Discover how rogue access points intercept unencrypted traffic, how VPNs encapsulate your data stream, and best practices for securing mobile devices on the go.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
                'estimated_minutes' => 25,
                'badge_name' => 'Network Sentinel',
                'order' => 4,
                'is_published' => true,
            ]
        );

        $module4_1 = CourseModule::updateOrCreate(
            ['course_id' => $course4->id, 'title' => 'Module 1: Untrusted Perimeters'],
            [
                'description' => 'Securing communications over hostile wireless frequencies.',
                'order' => 1,
            ]
        );

        $lesson4_1_1 = Lesson::updateOrCreate(
            ['course_module_id' => $module4_1->id, 'slug' => 'evil-twin-and-mitm-hotspots'],
            [
                'title' => 'Evil Twin Hotspots & Man-in-the-Middle Attacks',
                'type' => 'video',
                'duration_minutes' => 7,
                'video_url' => 'https://www.youtube.com/embed/bLz-R889-lQ',
                'threat_level' => 'high',
                'order' => 1,
                'is_published' => true,
                'key_takeaways' => [
                    'Anyone can broadcast a Wi-Fi network named "Starbucks Guest" or "Airport Free Wi-Fi" from a $50 pocket device.',
                    'Always verify HTTPS certificate warnings; never click "Accept Risk and Continue" on public Wi-Fi.',
                    'Use a trusted corporate VPN to tunnel all DNS queries and packet data securely.',
                ],
                'content' => "### The Dangers of Open Public Wi-Fi

Public wireless networks lack client isolation by default, meaning every computer connected to the same coffee shop router can see and intercept raw broadcast traffic from neighboring laptops.

#### Rogue Access Points (The 'Evil Twin')
An attacker sits in an airport lounge with a portable Wi-Fi Pineapple or laptop broadcasting the identical SSID as the terminal. Your smartphone or laptop, having previously saved that SSID, automatically connects without prompting you. Once connected, all your web traffic flows through the attacker's proxy.",
            ]
        );

        // Enroll test user in Course 1 with partial progress to showcase learner dashboard immediately!
        if ($user) {
            $enrollment = CourseEnrollment::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $course1->id],
                [
                    'progress_percentage' => 67,
                    'enrolled_at' => now()->subDays(2),
                    'last_accessed_at' => now()->subMinutes(15),
                    'certificate_code' => null,
                ]
            );

            LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson1_1_1->id],
                [
                    'is_completed' => true,
                    'completed_at' => now()->subDays(1),
                ]
            );

            LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson1_1_2->id],
                [
                    'is_completed' => true,
                    'completed_at' => now()->subHours(2),
                ]
            );
        }
    }
}
