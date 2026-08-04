# -*- coding: utf-8 -*-
"""
Inner First Aid — free guide content (EN + SL).

Content is evidence-informed (see "Sources" section in the PDF). All text is
plain strings with light inline markup: **bold**, *italic*.
"""

BRAND = {
    "name": "Inner First Aid",
    "domain": "innerfirstaid.com",
    "primary": "#1A4D2E",
    "accent": "#2D7A47",
    "dark": "#0D2E1C",
    "light": "#E8F5ED",
    "card": "#F0FAF3",
    "text": "#111111",
    "muted": "#5B6B62",
}

EN = {
    "lang_code": "EN",
    "cover": {
        "badge": "Free guide",
        "kicker": "Psychological first aid",
        "title": "When it hurts — here is step by step.",
        "subtitle": "The 3 mistakes that prolong the pain after a breakup — and what to do instead.",
        "footer": "innerfirstaid.com  ·  Free  ·  Anonymous",
    },
    "welcome": {
        "kicker": "Welcome",
        "title": "This guide is your first aid kit for heartbreak",
        "intro": (
            "A breakup hurts like an injury — because that is exactly what it is. "
            "Neuroscience shows that romantic rejection activates the same brain "
            "regions as physical pain. You are not weak; you are wounded. And "
            "wounds heal faster when you stop reopening them."
        ),
        "points_title": "What you will get from this guide",
        "points": [
            ("The 3 mistakes", "that silently keep the pain alive — and the science behind each one."),
            ("A first-aid toolkit", "six practical tools you can use the moment the wave hits."),
            ("A clear path forward", "the first 72 hours, plus a preview of the 21-day program."),
        ],
        "how_title": "The principle behind everything: Look, Listen, Link",
        "how_text": (
            "Psychological first aid, the framework this guide is built on, rests on three actions "
            "used by aid workers worldwide: **Look** — notice what is happening; **Listen** — let "
            "your feelings speak without judging them; **Link** — reconnect with people and "
            "routines that hold you up. This guide takes you through all three."
        ),
        "expect_title": "What NOT to expect",
        "expect": [
            "No promise that you will “get over it” in a week — recovery is not linear.",
            "No blaming — the goal is understanding, not guilt.",
            "No magic — only actions that research shows actually help.",
        ],
    },
    "mistakes_intro": {
        "kicker": "The 3 mistakes",
        "title": "Three habits that keep the wound open",
        "text": (
            "After a loss, your brain does what it is designed to do: it tries to make sense of the "
            "pain. But three common strategies — repeating, checking, and suppressing — trick the "
            "brain into treating the pain as an active threat, again and again. Here is what they "
            "are, why they hurt, and what to do instead."
        ),
    },
    "mistakes": [
        {
            "num": "01",
            "kicker": "Mistake 1",
            "title": "The replay loop — reliving the story on repeat",
            "signs": [
                "Replaying conversations and moments, asking “what if” and “why” for hours.",
                "Scrolling old messages and photos as if they were evidence to re-examine.",
                "Feeling drained after “thinking it through”, yet unable to stop.",
            ],
            "science_title": "Why it keeps hurting",
            "science": (
                "Decades of research on response styles show that **rumination — repetitive negative "
                "thinking about the loss — prolongs distress** and predicts worse recovery months "
                "later. The problem is not thinking about the relationship; it is the *way* you "
                "think: looping, without new answers, while the brain stays in alarm mode. "
                "Studies find rumination is one of the strongest predictors of emotional "
                "difficulty after a breakup — more than the breakup itself."
            ),
            "instead_title": "What to do instead",
            "instead": [
                ("Put grief in a container.", "Schedule one 20-minute “grief window” per day at the same time. Outside it, when the loop starts, say: “Not now — at 7 pm.” The thought still gets its turn; it just stops running your day."),
                ("Switch from “why” to “what”.", "“Why did this happen?” loops forever. “What did I learn, and what do I want now?” moves forward. Write one sentence of the second kind each day."),
                ("Do, don’t just think.", "Action interrupts the loop physically. A 10-minute walk, a task with your hands, a call to a friend — research on behavioral activation shows activity lifts mood where thinking alone cannot."),
            ],
            "quote": "Rumination feels like solving a problem. It is actually rehearsing a wound.",
        },
        {
            "num": "02",
            "kicker": "Mistake 2",
            "title": "Digital surveillance — checking their life online",
            "signs": [
                "Opening their profile “just once” — several times a day.",
                "Reading meaning into their posts, stories and online activity.",
                "Feeling worse after every check, but checking again within the hour.",
            ],
            "science_title": "Why it keeps hurting",
            "science": (
                "A series of studies on ex-partner surveillance found that **monitoring a former "
                "partner on social media is linked to more distress, more jealousy, more longing, "
                "and less personal growth** — and the effect shows up the same day and even the "
                "next day. It is not “just curiosity”: every check reactivates the attachment "
                "system and resets the clock on recovery."
            ),
            "instead_title": "What to do instead",
            "instead": [
                ("Create distance on purpose.", "Mute first (you can do it without unfriending). Then unfollow. Then, if the urge persists, block for 30 days. Distance is not cruelty — it is first aid."),
                ("Make checking cost something.", "Move the apps off your home screen; log out of accounts. When the urge appears, do the 90-second urge-surfing exercise (see toolkit) instead."),
                ("Feed the information vacuum.", "Your brain is scanning for news about them. Give it new input: one article, one podcast, one conversation with a real person — daily."),
            ],
            "quote": "Every check tells your brain: they still matter more than you do.",
        },
        {
            "num": "03",
            "kicker": "Mistake 3",
            "title": "Suppression — being “fine” when you are not",
            "signs": [
                "Telling yourself “I should be over this by now” and pushing feelings down.",
                "Staying busy non-stop so there is no quiet moment to feel.",
                "Smiling through it while the body carries tension, bad sleep, low energy.",
            ],
            "science_title": "Why it keeps hurting",
            "science": (
                "The classic “white bear” experiments showed that **the more you try to suppress a "
                "thought, the more it returns** — a phenomenon called the ironic rebound. Emotion "
                "research adds that suppression increases physical arousal and intensifies the "
                "emotion afterwards, while **naming an emotion (affect labeling) calms the brain’s "
                "alarm center** within seconds. Feelings you allow pass; feelings you trap stay."
            ),
            "instead_title": "What to do instead",
            "instead": [
                ("Name it to tame it.", "Say precisely what you feel: “I feel rejected”, “I feel lost”, “I feel relief and guilt at the same time”. Labeling moves activity from the alarm system to the thinking brain."),
                ("Give the feeling a body.", "Emotions live in the body. Sit with the feeling for 90 seconds, breathing slowly, and notice where it lives — chest, throat, stomach. It rises, peaks and falls — usually within a couple of minutes — when you stop feeding it with thoughts."),
                ("Speak it to one person.", "Expressive writing and talking both help — but they must be real. Write a letter you will never send, then burn or delete it. Tell one trusted person the honest version, not the brave one."),
            ],
            "quote": "What you resist, persists. What you allow, passes.",
        },
    ],
    "toolkit": {
        "kicker": "First-aid toolkit",
        "title": "Six tools for the moments it hits",
        "intro": (
            "Keep this page open on your phone. Each tool takes under two minutes. "
            "None of them erase the pain — they carry you through the worst of the wave, "
            "and that is enough."
        ),
        "tools": [
            {
                "title": "1 · The 5-4-3-2-1 grounding",
                "text": "When panic or flooding thoughts arrive, anchor yourself in the room:",
                "steps": [
                    "5 things you can see",
                    "4 things you can touch",
                    "3 things you can hear",
                    "2 things you can smell",
                    "1 thing you can taste",
                ],
                "note": "Count slowly, out loud if possible. It takes about 60 seconds.",
            },
            {
                "title": "2 · Box breathing",
                "text": "Breathe in for 4 counts, hold for 4, out for 4, hold for 4. Repeat 4–6 times.",
                "steps": [
                    "Inhale through the nose — 4 counts",
                    "Hold — 4 counts",
                    "Exhale slowly — 4 counts",
                    "Hold — 4 counts",
                ],
                "note": "This slows the heart rate and tells the nervous system the danger has passed.",
            },
            {
                "title": "3 · Urge surfing",
                "text": "An urge — to check their profile, to text them, to look at photos — behaves like a wave: it rises, peaks, and falls within minutes.",
                "steps": [
                    "Notice the urge and name it: “This is an urge to check.”",
                    "Do not fight it and do not follow it. Watch it.",
                    "Breathe and wait 90 seconds. The wave peaks and breaks.",
                ],
                "note": "You are not resisting the urge; you are riding it. The craving loses power every time you do.",
            },
            {
                "title": "4 · Name it to tame it",
                "text": "Use precise emotion words. “I feel bad” keeps the brain vague; “I feel ashamed” and “I feel lonely” give it something real to process.",
                "steps": [
                    "Pause and scan: what exactly am I feeling right now?",
                    "Pick the most precise word you can.",
                    "Say it silently: “This is grief”, “This is anger”, “This is fear”.",
                ],
                "note": "Studies show labeling an emotion calms the amygdala — the brain’s alarm — within seconds.",
            },
            {
                "title": "5 · The grief container",
                "text": "Schedule your pain instead of letting it schedule you.",
                "steps": [
                    "Choose a fixed 20-minute window each day (e.g. 7:00 pm).",
                    "During the day, postpone rumination: “Not now — at 7 pm.”",
                    "In the window, let everything out: think, write, cry if needed. When it ends, close the container.",
                ],
                "note": "The feeling is honoured, not suppressed — it just gets a boundary.",
            },
            {
                "title": "6 · The 30-day digital boundary",
                "text": "A complete break from their digital footprint, for one month.",
                "steps": [
                    "Mute or unfollow everywhere. No exceptions for “just checking stories”.",
                    "Log out of accounts you open reflexively.",
                    "If you break the boundary, do not punish yourself — restart the 30 days and move on.",
                ],
                "note": "Research links ex-partner surveillance to slower recovery. One month of distance is the single most effective tool in this guide.",
            },
        ],
    },
    "program": {
        "kicker": "The 21-day program",
        "title": "From first aid to recovery — one email per day",
        "intro": (
            "This guide stops the bleeding. The 21-day program rebuilds. One short email "
            "each morning walks you through the three phases below — with a daily action "
            "that takes 10–15 minutes."
        ),
        "phases": [
            {
                "title": "Week 1 — Stabilize",
                "text": "Feelings, sleep, food, routine. You learn to carry the pain instead of fighting it.",
                "items": ["Daily grief container", "Sleep & routine basics", "Emotion labeling practice"],
            },
            {
                "title": "Week 2 — Rebuild",
                "text": "Identity, friendships, activities. You rediscover who you are outside the relationship.",
                "items": ["Values & identity work", "Reconnecting with people", "New activities, small doses"],
            },
            {
                "title": "Week 3 — Look forward",
                "text": "Meaning, goals, growth. You turn the loss into a story that carries you forward.",
                "items": ["What this taught me", "Concrete goals for 90 days", "A letter to future me"],
            },
        ],
        "hours_title": "The first 72 hours — your immediate protocol",
        "hours": [
            ("Hour 0–24:", "Feel everything, decide nothing. Tell one trusted person. Eat, hydrate, sleep. No big decisions, no contact with them."),
            ("Day 2:", "Name the top three emotions with precise words. Mute/unfollow everywhere. Set your grief window time."),
            ("Day 3:", "Take one physical action — a walk, a workout, a task you have postponed. Write the letter you will not send. Book a coffee with a friend."),
        ],
    },
    "help": {
        "kicker": "You matter",
        "title": "When to reach for professional help",
        "text": (
            "This guide is educational first aid, not therapy. Please contact a professional if you:"
        ),
        "items": [
            "Feel unable to function — work, eating, or basic care are falling apart for more than two weeks.",
            "Have thoughts of self-harm or suicide — this is an emergency. Reach out now.",
            "Use alcohol, substances, or risky behaviour to numb the pain.",
            "Feel stuck in anger or despair that does not lift with time and support.",
        ],
        "resources_title": "Immediate help (EU)",
        "resources": [
            "Emergency services: 112 (any EU country)",
            "Emotional support helplines: 116 123 (free in many EU countries)",
            "Search “crisis helpline” + your country for local 24/7 support",
        ],
    },
    "sources": {
        "kicker": "Sources & further reading",
        "title": "Built on research",
        "items": [
            "Nolen-Hoeksema, S. (1991). Response styles theory of rumination and depression.",
            "Wegner, D. M. (1994). Ironic processes of mental control (the “white bear” effect).",
            "Gross, J. J. (2002). Emotion regulation: affective, cognitive, and social consequences.",
            "Lieberman, M. D. et al. (2007). Putting feelings into words: affect labeling.",
            "Marshall, T. C. (2012). Facebook surveillance of former romantic partners: associations with post-breakup recovery and personal growth.",
            "Pennebaker, J. W. (1997). Opening Up: The Healing Power of Expressing Emotions.",
            "Neff, K. D. (2011). Self-Compassion: The Proven Power of Being Kind to Yourself.",
            "World Health Organization (2011). Psychological First Aid: Guide for Field Workers.",
        ],
    },
    "back": {
        "title": "First aid today. A path forward tomorrow.",
        "text": (
            "You have the tools to stop the pain from growing. When you are ready for the "
            "next step, the 21-day program delivers one small, science-backed action to "
            "your inbox every morning — anonymous, step by step."
        ),
        "cta": "innerfirstaid.com",
        "disclaimer": (
            "This guide is for educational purposes only and is not a substitute for professional "
            "psychological, medical or therapeutic advice, diagnosis or treatment. If you are in "
            "crisis, contact emergency services or a local crisis helpline immediately."
        ),
        "copyright": "© 2026 Inner First Aid · All rights reserved",
    },
}

SL = {
    "lang_code": "SL",
    "cover": {
        "badge": "Brezplačni vodnik",
        "kicker": "Psihološka prva pomoč",
        "title": "Ko te boli — tu je korak za korakom.",
        "subtitle": "3 napake, ki podaljšajo bolečino po razhodu — in kaj narediti namesto njih.",
        "footer": "innerfirstaid.com  ·  Brezplačno  ·  Anonimno",
    },
    "welcome": {
        "kicker": "Dobrodošel/a",
        "title": "Ta vodnik je tvoj komplet prve pomoči za bolečino po razhodu",
        "intro": (
            "Razhod boli kot poškodba — ker tudi je. Znanost kaže, da romantična zavrnitev "
            "aktivira iste možganske predele kot fizična bolečina. Nisi šibek/a; ranjen/a si. "
            "In rane se celijo hitreje, ko jih nehaš znova odpirati."
        ),
        "points_title": "Kaj dobiš v tem vodniku",
        "points": [
            ("3 napake", "ki tiho ohranjajo bolečino pri življenju — in znanost za vsako od njih."),
            ("Komplet prve pomoči", "šest praktičnih orodij, ki jih uporabiš v trenutku, ko te val preplavi."),
            ("Jasno pot naprej", "prvih 72 ur in predogled 21-dnevnega programa."),
        ],
        "how_title": "Načelo za vsem: Poglej, Poslušaj, Poveži",
        "how_text": (
            "Psihološka prva pomoč, okvir, na katerem temelji ta vodnik, sloni na treh dejanjih, "
            "ki jih uporabljajo reševalci po vsem svetu: **Poglej** — opazi, kaj se dogaja; "
            "**Poslušaj** — dovoli občutkom, da spregovorijo, brez obsojanja; **Poveži** — znova "
            "se poveži z ljudmi in rutinami, ki te držijo pokonci. Ta vodnik te popelje skozi vse tri."
        ),
        "expect_title": "Česa NE pričakuj",
        "expect": [
            "Nobene obljube, da bo “v enem tednu mimo” — okrevanje ni ravna črta.",
            "Nobenega obtoževanja — cilj je razumevanje, ne krivda.",
            "Nobene čarovnije — samo dejanja, za katera raziskave kažejo, da res pomagajo.",
        ],
    },
    "mistakes_intro": {
        "kicker": "3 napake",
        "title": "Tri navade, ki držijo rano odprto",
        "text": (
            "Po izgubi tvoji možgani naredijo, kar so zasnovani narediti: poskušajo osmisliti "
            "bolečino. Toda tri pogoste strategije — ponavljanje, preverjanje in potlačitev — "
            "prepričajo možgane, da je bolečina še vedno aktivna grožnja, znova in znova. Tukaj "
            "je, kaj so, zakaj bolijo in kaj narediti namesto njih."
        ),
    },
    "mistakes": [
        {
            "num": "01",
            "kicker": "Napaka 1",
            "title": "Zanka ponavljanja — znova in znova preigravati zgodbo",
            "signs": [
                "Ure in ure preigravati pogovore in trenutke ter spraševati “kaj če” in “zakaj”.",
                "Brskati po starih sporočilih in fotografijah, kot da bi šlo za dokaze, ki jih je treba znova pregledati.",
                "Po “premisleku” se počutiti izčrpan/a, a ne moreš nehati.",
            ],
            "science_title": "Zakaj bolečina vztraja",
            "science": (
                "Desetletja raziskav o stilu odzivanja kažejo, da **prežvekovanje — ponavljajoče "
                "negativno razmišljanje o izgubi — podaljšuje stisko** in napoveduje slabše "
                "okrevanje mesece pozneje. Problem ni razmišljanje o zvezi; problem je *način* "
                "razmišljanja: zanka brez novih odgovorov, medtem ko možgani ostajajo v alarmnem "
                "stanju. Študije ugotavljajo, da je prežvekovanje eden najmočnejših napovednikov "
                "čustvenih težav po razhodu — močnejši od samega razhoda."
            ),
            "instead_title": "Kaj narediti namesto tega",
            "instead": [
                ("Daj žalosti posodo.", "Vsak dan ob isti uri načrtuj 20-minutno “okno za žalost”. Zunaj njega, ko se zanka začne, si reci: “Ne zdaj — ob 19. uri.” Misel še vedno dobi svoj čas; le ne vodi več tvojega dneva."),
                ("Preklopi iz “zakaj” v “kaj”.", "“Zakaj se je to zgodilo?” se vrti v neskončnost. “Kaj sem se naučil/a in kaj si zdaj želim?” gre naprej. Vsak dan zapiši en stavek druge vrste."),
                ("Delaj, ne samo premišljuj.", "Dejanje fizično prekine zanko. 10-minutni sprehod, opravek z rokami, klic prijatelja — raziskave o vedenjski aktivaciji kažejo, da aktivnost dvigne razpoloženje tam, kjer samo razmišljanje ne more."),
            ],
            "quote": "Prežvekovanje je videti kot reševanje problema. V resnici je vaja v ranah.",
        },
        {
            "num": "02",
            "kicker": "Napaka 2",
            "title": "Digitalno nadzorovanje — preverjati njihovo življenje na spletu",
            "signs": [
                "Odpreti njihov profil “samo enkrat” — večkrat na dan.",
                "Brati pomen v njihove objave, zgodbe in spletno dejavnost.",
                "Po vsakem preverjanju se počutiti slabše, a preveriti znova v eni uri.",
            ],
            "science_title": "Zakaj bolečina vztraja",
            "science": (
                "Raziskave o nadzorovanju bivšega partnerja so pokazale, da je **spremljanje "
                "bivšega na družbenih omrežjih povezano z več stiske, več ljubosumja, več hrepenenja "
                "in manj osebne rasti** — učinek se pokaže še isti dan in celo naslednji dan. Ni "
                "“samo radovednost”: vsako preverjanje znova aktivira navezanost in ponastavi "
                "uro okrevanja."
            ),
            "instead_title": "Kaj narediti namesto tega",
            "instead": [
                ("Ustvari razdaljo namenoma.", "Najprej izključi zvok (lahko brez odstranitve iz prijateljev). Nato prenehaj slediti. Če želja vztraja, blokiraj za 30 dni. Razdalja ni krutost — je prva pomoč."),
                ("Naredi preverjanje drago.", "Umakni aplikacije z začetnega zaslona; odjavi se iz računov. Ko se želja pojavi, naredi vajo 90-sekundnega jahanja želje (glej komplet orodij)."),
                ("Napolni informacijsko praznino.", "Tvoji možgani iščejo novice o njih. Daj jim nov vnos: en članek, en podcast, en pogovor z resnično osebo — dnevno."),
            ],
            "quote": "Vsako preverjanje pove tvojim možganom: oni so še vedno pomembnejši od tebe.",
        },
        {
            "num": "03",
            "kicker": "Napaka 3",
            "title": "Potlačitev — biti “v redu”, ko nisi",
            "signs": [
                "Govoriti si “do zdaj bi moral/a biti že čez to” in potiskati občutke navzdol.",
                "Nenehno biti zaposlen/a, da ni mirnega trenutka za čutenje.",
                "Smehljati se skozi vse, medtem ko telo nosi napetost, slab spanec, nizko energijo.",
            ],
            "science_title": "Zakaj bolečina vztraja",
            "science": (
                "Klasični poskusi z “belim medvedom” so pokazali, da **bolj ko poskušaš potlačiti "
                "misel, pogosteje se vrača** — pojav, imenovan ironični odboj. Raziskave o čustvih "
                "dodajajo, da potlačitev poveča telesno vzburjenost in pozneje okrepi čustvo, "
                "medtem ko **poimenovanje čustva (afektivno označevanje) v nekaj sekundah umiri "
                "alarmni center v možganih**. Občutki, ki jih dovoliš, minejo; občutki, ki jih "
                "ujameš, ostanejo."
            ),
            "instead_title": "Kaj narediti namesto tega",
            "instead": [
                ("Poimenuj, da ukrotiš.", "Natančno povej, kaj čutiš: “Počutim se zavrnjen/a”, “Počutim se izgubljen/a”, “Hkrati čutim olajšanje in krivdo”. Označevanje premakne dejavnost iz alarmnega sistema v misleče možgane."),
                ("Daj občutku telo.", "Čustva živijo v telesu. Ostani z občutkom 90 sekund, počasi dihaj in opazi, kje živi — v prsih, grlu, želodcu. Naraste, doseže vrh in pade — ponavadi v nekaj minutah — ko ga nehaš hraniti z mislimi."),
                ("Povej ga eni osebi.", "Pomagata tako izrazno pisanje kot pogovor — a morata biti iskrena. Napiši pismo, ki ga ne boš nikoli poslal/a, nato ga zažgi ali izbriši. Eni zaupanja vredni osebi povej iskreno različico, ne pogumne."),
            ],
            "quote": "Kar se upiraš, vztraja. Kar dovoliš, mine.",
        },
    ],
    "toolkit": {
        "kicker": "Komplet prve pomoči",
        "title": "Šest orodij za trenutke, ko te preplavi",
        "intro": (
            "To stran imej odprto na telefonu. Vsako orodje traja manj kot dve minuti. "
            "Nobeno ne izbriše bolečine — prenesejo te skozi najhujši del vala. In to je dovolj."
        ),
        "tools": [
            {
                "title": "1 · Priprava 5-4-3-2-1",
                "text": "Ko pride panika ali poplava misli, se zasidraj v prostor:",
                "steps": [
                    "5 stvari, ki jih vidiš",
                    "4 stvari, ki se jih lahko dotakneš",
                    "3 stvari, ki jih slišiš",
                    "2 stvari, ki jih lahko zavohaš",
                    "1 stvar, ki jo lahko okušaš",
                ],
                "note": "Štej počasi, po možnosti na glas. Traja približno 60 sekund.",
            },
            {
                "title": "2 · Kvadratno dihanje",
                "text": "Vdih 4 štete, zadrži 4, izdih 4, zadrži 4. Ponovi 4–6-krat.",
                "steps": [
                    "Vdih skozi nos — 4 štete",
                    "Zadrži — 4 štete",
                    "Počasi izdihni — 4 štete",
                    "Zadrži — 4 štete",
                ],
                "note": "To upočasni srčni utrip in sporoči živčnemu sistemu, da je nevarnost mimo.",
            },
            {
                "title": "3 · Jahanje želje",
                "text": "Želja — preveriti profil, pisati jim, pogledati fotografije — se obnaša kot val: naraste, doseže vrh in pade v nekaj minutah.",
                "steps": [
                    "Opazi željo in jo poimenuj: “To je želja po preverjanju.”",
                    "Ne bori se z njo in ji ne sledi. Opazuj jo.",
                    "Dihaj in počakaj 90 sekund. Val doseže vrh in se razbije.",
                ],
                "note": "Želji se ne upiraš; jahaš jo. Hrepenenje izgublja moč ob vsakem ponovljenem jahanju.",
            },
            {
                "title": "4 · Poimenuj, da ukrotiš",
                "text": "Uporabljaj natančne besede za čustva. “Počutim se slabo” pusti možgane nejasne; “sram me je” in “osamljen/a sem” jim da nekaj resničnega za obdelavo.",
                "steps": [
                    "Ustavi se in preišči: kaj točno čutim zdaj?",
                    "Izberi najbolj natančno besedo, ki jo najdeš.",
                    "Izreci jo v mislih: “To je žalost”, “To je jeza”, “To je strah”.",
                ],
                "note": "Študije kažejo, da poimenovanje čustva v nekaj sekundah umiri amigdalo — možganski alarm.",
            },
            {
                "title": "5 · Posoda za žalost",
                "text": "Načrtuj svojo bolečino, namesto da ona načrtuje tebe.",
                "steps": [
                    "Vsak dan izberi fiksno 20-minutno okno (npr. ob 19. uri).",
                    "Čez dan preloži prežvekovanje: “Ne zdaj — ob 19. uri.”",
                    "V oknu spusti vse ven: misli, piši, jokaj, če je treba. Ko se okno zapre, zapri posodo.",
                ],
                "note": "Občutek je spoštovan, ne potlačen — le dobi mejo.",
            },
            {
                "title": "6 · 30-dnevna digitalna meja",
                "text": "Popoln premor od njihove digitalne sledi, za en mesec.",
                "steps": [
                    "Povsod izključi zvok ali prenehaj slediti. Brez izjem za “samo pogledam zgodbe”.",
                    "Odjavi se iz računov, ki jih odpiraš refleksno.",
                    "Če mejo prekršiš, se ne kaznuj — znova začni 30 dni in pojdi naprej.",
                ],
                "note": "Raziskave povezujejo nadzorovanje bivšega s počasnejšim okrevanjem. En mesec razdalje je najučinkovitejše orodje v tem vodniku.",
            },
        ],
    },
    "program": {
        "kicker": "21-dnevni program",
        "title": "Od prve pomoči do okrevanja — en email na dan",
        "intro": (
            "Ta vodnik ustavi krvavitev. 21-dnevni program gradi znova. Vsako jutro te kratek "
            "email popelje skozi tri faze spodaj — z dnevnim dejanjem, ki traja 10–15 minut."
        ),
        "phases": [
            {
                "title": "1. teden — Stabilizacija",
                "text": "Občutki, spanje, hrana, rutina. Naučiš se nositi bolečino namesto bojevati se z njo.",
                "items": ["Dnevna posoda za žalost", "Osnove spanca in rutine", "Vaja poimenovanja čustev"],
            },
            {
                "title": "2. teden — Ponovna izgradnja",
                "text": "Identiteta, prijateljstva, dejavnosti. Znova odkriješ, kdo si zunaj zveze.",
                "items": ["Delo z vrednotami in identiteto", "Ponovno povezovanje z ljudmi", "Nove dejavnosti v majhnih odmerkih"],
            },
            {
                "title": "3. teden — Pogled naprej",
                "text": "Smisel, cilji, rast. Izgubo spremeniš v zgodbo, ki te nosi naprej.",
                "items": ["Kaj me je to naučilo", "Konkretni cilji za 90 dni", "Pismo bodočemu sebi"],
            },
        ],
        "hours_title": "Prvih 72 ur — takojšnji protokol",
        "hours": [
            ("Ura 0–24:", "Čuti vse, ne odločaj ničesar. Povej eni zaupanja vredni osebi. Jej, pij, spi. Brez velikih odločitev, brez stika z njimi."),
            ("2. dan:", "Poimenuj tri najmočnejša čustva z natančnimi besedami. Povsod izključi zvok ali prenehaj slediti. Določi čas za svoje okno žalosti."),
            ("3. dan:", "Naredi eno fizično dejanje — sprehod, vadbo, opravek, ki si ga odlašal/a. Napiši pismo, ki ga ne boš poslal/a. Dogovori se za kavo s prijateljem."),
        ],
    },
    "help": {
        "kicker": "Ti si pomemben/a",
        "title": "Kdaj poiskati strokovno pomoč",
        "text": "Ta vodnik je izobraževalna prva pomoč, ne terapija. Prosim, poišči strokovnjaka, če:",
        "items": [
            "Se počutiš nesposobnega/na delovati — delo, prehranjevanje ali osnovna skrb zase razpadajo več kot dva tedna.",
            "Imaš misli o samopoškodovanju ali samomoru — to je nujen primer. Stopi v stik zdaj.",
            "Za otopitev bolečine uporabljaš alkohol, substance ali tvegano vedenje.",
            "Obtičiš v jezi ali obupu, ki se ne umakne s časom in podporo.",
        ],
        "resources_title": "Takojšnja pomoč (EU)",
        "resources": [
            "Nujna pomoč: 112 (v kateri koli državi EU)",
            "Telefoni za čustveno podporo: 116 123 (brezplačno v mnogih državah EU)",
            "Poišči “krizna linija” + svojo državo za lokalno 24/7 podporo",
        ],
    },
    "sources": {
        "kicker": "Viri in nadaljnje branje",
        "title": "Zgrajeno na raziskavah",
        "items": [
            "Nolen-Hoeksema, S. (1991). Response styles theory of rumination and depression.",
            "Wegner, D. M. (1994). Ironic processes of mental control (učinek “belega medveda”).",
            "Gross, J. J. (2002). Emotion regulation: affective, cognitive, and social consequences.",
            "Lieberman, M. D. et al. (2007). Putting feelings into words: affect labeling.",
            "Marshall, T. C. (2012). Facebook surveillance of former romantic partners: associations with post-breakup recovery and personal growth.",
            "Pennebaker, J. W. (1997). Opening Up: The Healing Power of Expressing Emotions.",
            "Neff, K. D. (2011). Self-Compassion: The Proven Power of Being Kind to Yourself.",
            "World Health Organization (2011). Psychological First Aid: Guide for Field Workers.",
        ],
    },
    "back": {
        "title": "Prva pomoč danes. Pot naprej jutri.",
        "text": (
            "Zdaj imaš orodja, da bolečini preprečiš rast. Ko si pripravljen/a na naslednji korak, "
            "ti 21-dnevni program vsako jutro pošlje eno majhno, znanstveno podprto dejanje — "
            "anonimno, korak za korakom."
        ),
        "cta": "innerfirstaid.com",
        "disclaimer": (
            "Ta vodnik je namenjen izobraževalnim namenom in ni nadomestilo za strokovno "
            "psihološko, medicinsko ali terapevtsko pomoč, diagnozo ali zdravljenje. Če si v "
            "krizi, takoj pokliči nujno pomoč ali lokalno krizno linijo."
        ),
        "copyright": "© 2026 Inner First Aid · Vse pravice pridržane",
    },
}
