#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Build the Inner First Aid free guide PDFs (EN + SL).

Modern, production-ready design: A4, Inter typography, brand palette,
cover page with artwork, cards, science boxes, pull quotes, toolkit cards.

Usage:
    python3 build_guide.py [out_dir]

Outputs:
    out_dir/Inner-First-Aid-Guide-EN.pdf
    out_dir/Inner-First-Aid-Guide-SL.pdf
"""

import os
import re
import sys

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT, TA_CENTER
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    Image,
    NextPageTemplate,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
    KeepTogether,
)

from guide_content import BRAND, EN, SL

HERE = os.path.dirname(os.path.abspath(__file__))
FONT_DIR = os.path.join(HERE, "assets", "fonts")
ART = os.path.join(HERE, "assets", "cover-art.jpg")
OUT_DIR = sys.argv[1] if len(sys.argv) > 1 else HERE

PAGE_W, PAGE_H = A4
MARGIN = 16 * mm
FRAME_W = PAGE_W - 2 * MARGIN
CONTENT_W = FRAME_W

C = {k: colors.HexColor(v) for k, v in BRAND.items() if v.startswith("#")}

# ---------------------------------------------------------------------------
# Fonts
# ---------------------------------------------------------------------------
pdfmetrics.registerFont(TTFont("Inter", os.path.join(FONT_DIR, "Inter-400.ttf")))
pdfmetrics.registerFont(TTFont("Inter-Italic", os.path.join(FONT_DIR, "Inter-400-Italic.ttf")))
pdfmetrics.registerFont(TTFont("Inter-Med", os.path.join(FONT_DIR, "Inter-500.ttf")))
pdfmetrics.registerFont(TTFont("Inter-MedIt", os.path.join(FONT_DIR, "Inter-500-Italic.ttf")))
pdfmetrics.registerFont(TTFont("Inter-Semi", os.path.join(FONT_DIR, "Inter-600.ttf")))
pdfmetrics.registerFont(TTFont("Inter-Bold", os.path.join(FONT_DIR, "Inter-700.ttf")))
pdfmetrics.registerFont(TTFont("Inter-BoldIt", os.path.join(FONT_DIR, "Inter-700-Italic.ttf")))
pdfmetrics.registerFont(TTFont("Inter-XBold", os.path.join(FONT_DIR, "Inter-800.ttf")))

pdfmetrics.registerFontFamily(
    "Inter",
    normal="Inter",
    bold="Inter-Bold",
    italic="Inter-Italic",
    boldItalic="Inter-BoldIt",
)
pdfmetrics.registerFontFamily(
    "Inter-Med",
    normal="Inter-Med",
    bold="Inter-Bold",
    italic="Inter-MedIt",
    boldItalic="Inter-BoldIt",
)
pdfmetrics.registerFontFamily(
    "Inter-Semi",
    normal="Inter-Semi",
    bold="Inter-Bold",
    italic="Inter-BoldIt",
    boldItalic="Inter-BoldIt",
)

# ---------------------------------------------------------------------------
# Styles
# ---------------------------------------------------------------------------
def st(name, **kw):
    base = dict(
        fontName="Inter",
        fontSize=10.5,
        leading=15.5,
        textColor=C["text"],
        alignment=TA_LEFT,
        spaceBefore=0,
        spaceAfter=0,
    )
    base.update(kw)
    return ParagraphStyle(name, **base)


S = {
    "kicker": st("kicker", fontName="Inter-Bold", fontSize=9, leading=12,
                 textColor=C["accent"], spaceAfter=6),
    "title": st("title", fontName="Inter-XBold", fontSize=21, leading=25,
                textColor=C["dark"], spaceAfter=8),
    "title_sm": st("title_sm", fontName="Inter-XBold", fontSize=15.5, leading=19,
                   textColor=C["dark"], spaceAfter=4),
    "body": st("body", fontSize=10.5, leading=15.8, spaceAfter=8),
    "body_sm": st("body_sm", fontSize=9.8, leading=14.4, spaceAfter=6),
    "card_title": st("card_title", fontName="Inter-Semi", fontSize=11.5, leading=15,
                     textColor=C["dark"], spaceAfter=3),
    "quote": st("quote", fontName="Inter-MedIt", fontSize=12.5, leading=17.5,
                textColor=C["primary"], spaceAfter=0),
    "white": st("white", fontSize=10.5, leading=15.8, textColor=colors.white, spaceAfter=6),
    "white_bold": st("white_bold", fontName="Inter-Bold", fontSize=10.5, leading=15,
                     textColor=colors.white, spaceAfter=3),
    "white_sm": st("white_sm", fontSize=9.3, leading=13.6, textColor=colors.HexColor("#cfe3d8"),
                   spaceAfter=5),
    "note": st("note", fontName="Inter-Italic", fontSize=9.3, leading=13.5,
               textColor=C["muted"], spaceAfter=0),
    "phase_title": st("phase_title", fontName="Inter-Bold", fontSize=11, leading=14,
                      textColor=C["dark"], spaceAfter=3),
    "phase_text": st("phase_text", fontSize=9.3, leading=13.6, textColor=C["muted"], spaceAfter=4),
    "phase_item": st("phase_item", fontSize=9.3, leading=13.4, textColor=C["text"], spaceAfter=2),
    "source": st("source", fontSize=8.4, leading=12.2, textColor=C["muted"], spaceAfter=3),
    "footnote": st("footnote", fontSize=8.2, leading=11.5, textColor=C["muted"], spaceAfter=0),
}


def md(text):
    """Convert **bold** / *italic* to reportlab markup, escaping XML first."""
    text = text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    text = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", text)
    text = re.sub(r"(?<!\*)\*([^*]+?)\*(?!\*)", r"<i>\1</i>", text)
    return text


# ---------------------------------------------------------------------------
# Small building blocks
# ---------------------------------------------------------------------------
def pill(text, fg, border, bg=None):
    """Rounded pill label."""
    style = st("pill", fontName="Inter-Bold", fontSize=8.5, leading=11, textColor=fg,
               alignment=TA_CENTER, spaceBefore=0, spaceAfter=0)
    inner = Paragraph(text.upper(), style)
    t = Table([[inner]], colWidths=[None], style=TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), bg or colors.transparent),
        ("BOX", (0, 0), (-1, -1), 1.1, border),
        ("LINEBEFORE", (0, 0), (0, -1), 0, colors.transparent),
        ("LINEAFTER", (-1, 0), (-1, -1), 0, colors.transparent),
        ("TOPPADDING", (0, 0), (-1, -1), 4),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
        ("LEFTPADDING", (0, 0), (-1, -1), 10),
        ("RIGHTPADDING", (0, 0), (-1, -1), 10),
    ]))
    t.hAlign = "LEFT"
    return t


def section_header(kicker, title):
    return [
        pill(kicker, C["accent"], C["accent"], C["light"]),
        Spacer(1, 7),
        Paragraph(md(title), S["title"]),
        Spacer(1, 6),
    ]


def bullet_item(marker, title, text, marker_bg=C["accent"], marker_fg=colors.white,
                title_font="Inter-Bold", body_style="body_sm", width=None):
    if width is None:
        # default: inside a card with pad=13
        width = CONTENT_W - 20 - 26 - 34
    m = Paragraph(marker, st("m", fontName="Inter-Bold", fontSize=10.5, leading=13,
                             textColor=marker_fg, alignment=TA_CENTER))
    mt = Table([[m]], colWidths=[16], rowHeights=[16], style=TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), marker_bg),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("LEFTPADDING", (0, 0), (-1, -1), 0),
        ("RIGHTPADDING", (0, 0), (-1, -1), 0),
        ("TOPPADDING", (0, 0), (-1, -1), 0),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 0),
    ]))
    title_p = Paragraph(md(title), st("bt", fontName=title_font, fontSize=10.8, leading=14,
                                      textColor=C["dark"], spaceAfter=2))
    text_p = Paragraph(md(text), S[body_style])
    inner = Table([[title_p], [text_p]], colWidths=[width], style=TableStyle([
        ("LEFTPADDING", (0, 0), (-1, -1), 0),
        ("RIGHTPADDING", (0, 0), (-1, -1), 0),
        ("TOPPADDING", (0, 0), (-1, -1), 0),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 0),
    ]))
    row = Table([[mt, inner]], colWidths=[24, width], style=TableStyle([
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (0, -1), 0),
        ("RIGHTPADDING", (0, 0), (0, -1), 10),
        ("TOPPADDING", (0, 0), (0, -1), 1),
        ("LEFTPADDING", (1, 0), (1, -1), 0),
        ("RIGHTPADDING", (1, 0), (1, -1), 0),
        ("TOPPADDING", (1, 0), (1, -1), 0),
        ("BOTTOMPADDING", (1, 0), (1, -1), 0),
    ]))
    return row


def check_row(text):
    box = Table([[""]], colWidths=[10], rowHeights=[10], style=TableStyle([
        ("BOX", (0, 0), (-1, -1), 1.2, C["accent"]),
        ("BACKGROUND", (0, 0), (-1, -1), C["light"]),
        ("LEFTPADDING", (0, 0), (-1, -1), 0),
        ("RIGHTPADDING", (0, 0), (-1, -1), 0),
        ("TOPPADDING", (0, 0), (-1, -1), 0),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 0),
    ]))
    p = Paragraph(md(text), st("ck", fontSize=10.3, leading=14.2, textColor=C["text"]))
    t = Table([[box, p]], colWidths=[18, None], style=TableStyle([
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (0, -1), 0),
        ("RIGHTPADDING", (0, 0), (0, -1), 8),
        ("TOPPADDING", (0, 0), (0, -1), 2),
        ("LEFTPADDING", (1, 0), (1, -1), 0),
        ("RIGHTPADDING", (1, 0), (1, -1), 0),
        ("TOPPADDING", (1, 0), (1, -1), 0),
        ("BOTTOMPADDING", (1, 0), (1, -1), 0),
    ]))
    return t


def card(children, bg=C["card"], bar=C["accent"], pad=12, spacing=0):
    """Flat card with a left accent bar. `children` is a list of flowables."""
    inner_w = CONTENT_W - 20 - 2 * pad
    t = Table([[children]], colWidths=[inner_w], style=TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), bg),
        ("LINEBEFORE", (0, 0), (0, -1), 3, bar),
        ("LEFTPADDING", (0, 0), (-1, -1), pad),
        ("RIGHTPADDING", (0, 0), (-1, -1), pad),
        ("TOPPADDING", (0, 0), (-1, -1), pad),
        ("BOTTOMPADDING", (0, 0), (-1, -1), pad),
    ]))
    return t


def science_box(title, text):
    rows = [
        Table([[Paragraph(md(title), S["white_bold"])]], colWidths=[CONTENT_W - 32]),
        Spacer(1, 4),
        Table([[Paragraph(md(text), S["white"])]], colWidths=[CONTENT_W - 32]),
    ]
    t = Table([[rows]], colWidths=[CONTENT_W], style=TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), C["dark"]),
        ("LEFTPADDING", (0, 0), (-1, -1), 16),
        ("RIGHTPADDING", (0, 0), (-1, -1), 16),
        ("TOPPADDING", (0, 0), (-1, -1), 14),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 14),
    ]))
    return t


def quote_block(text):
    t = Table([[Paragraph(md("“" + text + "”"), S["quote"])]], colWidths=[CONTENT_W - 30],
              style=TableStyle([
                  ("LINEBEFORE", (0, 0), (0, -1), 3, C["accent"]),
                  ("LEFTPADDING", (0, 0), (-1, -1), 16),
                  ("RIGHTPADDING", (0, 0), (-1, -1), 8),
                  ("TOPPADDING", (0, 0), (-1, -1), 6),
                  ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
              ]))
    return t


def spacer(h):
    return Spacer(1, h)


# ---------------------------------------------------------------------------
# Page furniture
# ---------------------------------------------------------------------------
def draw_brand(cv, x, y, size=11, fg=None):
    fg = fg or C["primary"]
    cv.saveState()
    cv.setStrokeColor(fg)
    cv.setLineWidth(1.3)
    cv.circle(x + 5, y - 5, 5, stroke=1, fill=0)
    cv.setFillColor(fg)
    cv.setLineWidth(1.1)
    cv.line(x + 3, y - 7.5, x + 7, y - 2.5)
    cv.line(x + 3, y - 2.5, x + 7, y - 7.5)
    cv.restoreState()
    cv.setFont("Inter-Semi", 9.5)
    cv.setFillColor(fg)
    cv.drawString(x + 14, y - 8.5, "innerfirstaid.com")


def on_content_page(cv, doc):
    cv.saveState()
    # header
    cv.setStrokeColor(C["light"])
    cv.setLineWidth(1)
    cv.line(MARGIN, PAGE_H - 14 * mm, PAGE_W - MARGIN, PAGE_H - 14 * mm)
    draw_brand(cv, MARGIN, PAGE_H - 9.5 * mm)
    cv.setFont("Inter-Med", 8.5)
    cv.setFillColor(C["muted"])
    cv.drawRightString(PAGE_W - MARGIN, PAGE_H - 9.5 * mm, doc.lang_header_label)
    # footer
    cv.setStrokeColor(C["light"])
    cv.line(MARGIN, 12 * mm, PAGE_W - MARGIN, 12 * mm)
    cv.setFont("Inter-Med", 8.5)
    cv.setFillColor(C["muted"])
    cv.drawCentredString(PAGE_W / 2, 8.5 * mm, f"{doc.page}")
    cv.restoreState()


def cover_art_clipped(cv, x, y, w, h, radius=14):
    """Draw the cover art with rounded corners."""
    from reportlab.lib.utils import ImageReader
    img = ImageReader(ART)
    iw, ih = img.getSize()
    scale = max(w / iw, h / ih)
    dw, dh = iw * scale, ih * scale
    dx = x + (w - dw) / 2
    dy = y + (h - dh) / 2
    cv.saveState()
    p = cv.beginPath()
    p.roundRect(x, y, w, h, radius)
    cv.clipPath(p, stroke=0, fill=0)
    cv.drawImage(img, dx, dy, dw, dh, mask="auto")
    cv.restoreState()


def on_cover_page(cv, doc):
    cv.saveState()
    cv.setFillColor(C["dark"])
    cv.rect(0, 0, PAGE_W, PAGE_H, stroke=0, fill=1)

    # artwork band
    art_h = 132 * mm
    art_w = PAGE_W - 26 * mm
    cover_art_clipped(cv, 13 * mm, PAGE_H - 16 * mm - art_h, art_w, art_h, radius=10)

    # badge pill
    cv.setFont("Inter-Bold", 9)
    cv.setFillColor(C["accent"])
    w = cv.stringWidth(doc.lang_cover_badge.upper(), "Inter-Bold", 9)
    cv.roundRect(MARGIN, PAGE_H - 16 * mm - art_h - 14 * mm, w + 22, 20, 10, stroke=0, fill=1)
    cv.setFillColor(colors.white)
    cv.drawCentredString(MARGIN + 11 + w / 2, PAGE_H - 16 * mm - art_h - 9 * mm, doc.lang_cover_badge.upper())

    # kicker
    cv.setFont("Inter-Semi", 11)
    cv.setFillColor(C["accent"])
    cv.drawString(MARGIN, PAGE_H - 16 * mm - art_h - 26 * mm, doc.lang_cover_kicker.upper())

    # title
    cv.setFont("Inter-XBold", 30)
    cv.setFillColor(colors.white)
    y = PAGE_H - 16 * mm - art_h - 34 * mm
    for line in doc.lang_cover_title.split("\n"):
        cv.drawString(MARGIN, y, line)
        y -= 15.5 * mm

    # subtitle
    cv.setFont("Inter", 12.5)
    cv.setFillColor(colors.HexColor("#cfe3d8"))
    y -= 2 * mm
    for line in doc.lang_cover_subtitle.split("\n"):
        cv.drawString(MARGIN, y, line)
        y -= 6.5 * mm

    # footer
    cv.setFont("Inter-Med", 9.5)
    cv.setFillColor(colors.HexColor("#8fb3a0"))
    cv.drawCentredString(PAGE_W / 2, 18 * mm, doc.lang_cover_footer)
    cv.restoreState()


# ---------------------------------------------------------------------------
# Document assembly
# ---------------------------------------------------------------------------
class GuideDoc(BaseDocTemplate):
    lang_cover_badge = ""
    lang_cover_kicker = ""
    lang_cover_title = ""
    lang_cover_subtitle = ""
    lang_cover_footer = ""
    lang_header_label = "Psychological first aid"


def build(lang, data, out_path):
    doc = GuideDoc(
        out_path,
        pagesize=A4,
        leftMargin=MARGIN,
        rightMargin=MARGIN,
        topMargin=20 * mm,
        bottomMargin=16 * mm,
        title=f"Inner First Aid — Free Guide ({data['lang_code']})",
        author="Inner First Aid",
    )
    doc.lang_cover_badge = data["cover"]["badge"]
    doc.lang_cover_kicker = data["cover"]["kicker"]
    doc.lang_cover_title = data["cover"]["title"]
    doc.lang_cover_subtitle = data["cover"]["subtitle"]
    doc.lang_cover_footer = data["cover"]["footer"]
    doc.lang_header_label = data["cover"]["kicker"]

    frame = Frame(MARGIN, 16 * mm, FRAME_W, PAGE_H - 20 * mm - 16 * mm, id="content")
    doc.addPageTemplates([
        PageTemplate(id="cover", frames=[Frame(0, 0, PAGE_W, PAGE_H, id="coverframe")], onPage=on_cover_page),
        PageTemplate(id="content", frames=[frame], onPage=on_content_page),
    ])

    story = []
    story.append(Spacer(1, 1))  # cover page content is drawn on canvas
    story.append(NextPageTemplate("content"))
    story.append(PageBreak())

    # ---------------- Welcome ----------------
    w = data["welcome"]
    story += section_header(w["kicker"], w["title"])
    story.append(Paragraph(md(w["intro"]), S["body"]))
    story.append(spacer(4))
    story.append(Paragraph(md(w["points_title"]), S["card_title"]))
    story.append(spacer(3))
    pts = []
    for i, (t, d) in enumerate(w["points"], 1):
        pts.append(bullet_item(str(i), t, d))
        pts.append(spacer(6))
    story.append(card(pts, bg=C["light"], bar=C["primary"], pad=14))
    story.append(spacer(14))
    story.append(Paragraph(md(w["how_title"]), S["card_title"]))
    story.append(spacer(3))
    story.append(Paragraph(md(w["how_text"]), S["body_sm"]))
    story.append(spacer(14))
    story.append(Paragraph(md(w["expect_title"]), S["card_title"]))
    story.append(spacer(3))
    for item in w["expect"]:
        story.append(check_row(item))
        story.append(spacer(5))
    story.append(PageBreak())

    # ---------------- The 3 mistakes ----------------
    mi = data["mistakes_intro"]
    story += section_header(mi["kicker"], mi["title"])
    story.append(Paragraph(md(mi["text"]), S["body"]))
    story.append(spacer(8))

    for idx, m in enumerate(data["mistakes"]):
        block = []
        block.append(pill(m["kicker"] + "  ·  " + m["num"], C["accent"], C["accent"], C["light"]))
        block.append(spacer(7))
        block.append(Paragraph(md(m["title"]), S["title_sm"]))
        block.append(spacer(6))

        # signs card
        sign_rows = []
        for s in m["signs"]:
            sign_rows.append(check_row(s))
            sign_rows.append(spacer(5))
        block.append(card(sign_rows, bg=C["card"], bar=C["accent"], pad=13))
        block.append(spacer(10))

        # science box
        block.append(science_box(m["science_title"], m["science"]))
        block.append(spacer(10))

        # instead
        block.append(Paragraph(md(m["instead_title"]), S["card_title"]))
        block.append(spacer(4))
        for i, (t, d) in enumerate(m["instead"], 1):
            block.append(bullet_item(str(i), t, d))
            block.append(spacer(7))
        block.append(quote_block(m["quote"]))
        story.append(KeepTogether(block))
        if idx < len(data["mistakes"]) - 1:
            story.append(spacer(14))
            story.append(PageBreak())
        else:
            story.append(spacer(6))
    story.append(PageBreak())

    # ---------------- Toolkit ----------------
    tk = data["toolkit"]
    story += section_header(tk["kicker"], tk["title"])
    story.append(Paragraph(md(tk["intro"]), S["body"]))
    story.append(spacer(8))
    for tool in tk["tools"]:
        rows = [Paragraph(md(tool["title"]), S["card_title"]), Spacer(1, 2)]
        rows.append(Paragraph(md(tool["text"]), S["body_sm"]))
        rows.append(Spacer(1, 3))
        for step in tool["steps"]:
            rows.append(check_row(step))
            rows.append(Spacer(1, 4))
        rows.append(Spacer(1, 1))
        rows.append(Paragraph(md(tool["note"]), S["note"]))
        story.append(KeepTogether(card(rows, bg=C["card"], bar=C["primary"], pad=13)))
        story.append(spacer(9))
    story.append(PageBreak())

    # ---------------- Program ----------------
    pr = data["program"]
    story += section_header(pr["kicker"], pr["title"])
    story.append(Paragraph(md(pr["intro"]), S["body"]))
    story.append(spacer(8))

    # phases: 3 cards side by side
    phase_cells = []
    cell_w = (CONTENT_W - 24) / 3
    inner_w = cell_w - 22  # cell paddings
    for ph in pr["phases"]:
        rows = [Paragraph(md(ph["title"]), S["phase_title"]),
                Paragraph(md(ph["text"]), S["phase_text"]), Spacer(1, 4)]
        for it in ph["items"]:
            rows.append(Paragraph("•  " + md(it), S["phase_item"]))
        cell = Table([[rows]], colWidths=[inner_w], style=TableStyle([
            ("BACKGROUND", (0, 0), (-1, -1), C["light"]),
            ("LINEABOVE", (0, 0), (-1, 0), 3, C["accent"]),
            ("LEFTPADDING", (0, 0), (-1, -1), 11),
            ("RIGHTPADDING", (0, 0), (-1, -1), 11),
            ("TOPPADDING", (0, 0), (-1, -1), 12),
            ("BOTTOMPADDING", (0, 0), (-1, -1), 12),
        ]))
        phase_cells.append(cell)
    phases_t = Table([phase_cells], colWidths=[cell_w] * 3, style=TableStyle([
        ("LEFTPADDING", (0, 0), (-1, -1), 0),
        ("RIGHTPADDING", (0, 0), (-1, -1), 0),
        ("TOPPADDING", (0, 0), (-1, -1), 0),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 0),
        ("LEFTPADDING", (1, 0), (1, 0), 6),
        ("RIGHTPADDING", (0, 0), (0, 0), 6),
    ]))
    story.append(phases_t)
    story.append(spacer(18))

    story.append(Paragraph(md(pr["hours_title"]), S["title_sm"]))
    story.append(spacer(6))
    rows = []
    for t, d in pr["hours"]:
        rows.append(bullet_item("!", t, d, marker_bg=C["primary"]))
        rows.append(spacer(7))
    story.append(card(rows, bg=C["card"], bar=C["primary"], pad=14))
    story.append(spacer(14))

    # ---------------- Help ----------------
    h = data["help"]
    story += section_header(h["kicker"], h["title"])
    story.append(Paragraph(md(h["text"]), S["body"]))
    story.append(spacer(4))
    for item in h["items"]:
        story.append(check_row(item))
        story.append(spacer(5))
    story.append(spacer(10))
    story.append(Paragraph(md(h["resources_title"]), S["card_title"]))
    story.append(spacer(4))
    for r in h["resources"]:
        story.append(bullet_item("•", r, "", marker_bg=C["accent"], marker_fg=colors.white))
        story.append(spacer(5))
    story.append(spacer(14))

    # ---------------- Sources ----------------
    sc = data["sources"]
    story += section_header(sc["kicker"], sc["title"])
    story.append(spacer(2))
    for s in sc["items"]:
        story.append(Paragraph("•  " + md(s), S["source"]))
        story.append(spacer(2))
    story.append(spacer(6))
    story.append(Paragraph(
        md("This guide summarises established findings for educational purposes. "
           "Individual results vary; when in doubt, seek professional support."),
        S["footnote"]))

    # ---------------- Back cover ----------------
    bk = data["back"]
    story.append(NextPageTemplate("back"))
    story.append(PageBreak())
    story.append(Spacer(1, 1))

    def on_back(cv, d):
        cv.saveState()
        cv.setFillColor(C["dark"])
        cv.rect(0, 0, PAGE_W, PAGE_H, stroke=0, fill=1)
        cv.setFillColor(colors.white)
        cv.setFont("Inter-XBold", 24)
        y = PAGE_H - 90 * mm
        for line in bk["title"].split("\n"):
            cv.drawCentredString(PAGE_W / 2, y, line)
            y -= 12 * mm
        cv.setFont("Inter", 12)
        cv.setFillColor(colors.HexColor("#cfe3d8"))
        y -= 6 * mm
        for line in bk["text"].split("\n"):
            cv.drawCentredString(PAGE_W / 2, y, line)
            y -= 6.2 * mm
        # CTA pill
        cv.setFont("Inter-Bold", 11)
        w = cv.stringWidth(bk["cta"], "Inter-Bold", 11)
        cv.setFillColor(C["accent"])
        cv.roundRect(PAGE_W / 2 - w / 2 - 16, y - 16, w + 32, 26, 13, stroke=0, fill=1)
        cv.setFillColor(colors.white)
        cv.drawCentredString(PAGE_W / 2, y - 6, bk["cta"])
        y -= 34 * mm
        cv.setFont("Inter", 8.5)
        cv.setFillColor(colors.HexColor("#8fb3a0"))
        for line in bk["disclaimer"].split("\n"):
            cv.drawCentredString(PAGE_W / 2, y, line)
            y -= 4.2 * mm
        cv.setFont("Inter-Med", 8.5)
        cv.drawCentredString(PAGE_W / 2, 16 * mm, bk["copyright"])
        cv.restoreState()

    back_template = PageTemplate(id="back", frames=[Frame(0, 0, PAGE_W, PAGE_H, id="bf")], onPage=on_back)
    doc.addPageTemplates([back_template])
    doc.build(story)
    print(f"Built {out_path}")


def main():
    os.makedirs(OUT_DIR, exist_ok=True)
    build("en", EN, os.path.join(OUT_DIR, "Inner-First-Aid-Guide-EN.pdf"))
    build("sl", SL, os.path.join(OUT_DIR, "Inner-First-Aid-Guide-SL.pdf"))


if __name__ == "__main__":
    main()
