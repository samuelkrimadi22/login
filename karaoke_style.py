#!/usr/bin/env python3
"""
karaoke_style.py - V9.57.0
Generate ASS subtitle dengan karaoke effect (word-by-word highlight).
Styles: tiktok, neon, simple, mrbeast
"""

import os

KARAOKE_STYLES = {
    "tiktok": {
        "name": "TikTok Classic",
        "font": "Rubik-Bold",
        "font_size": 72,
        "primary": "&H0000FFFF",      # Yellow (active)
        "secondary": "&H00FFFFFF",    # White (inactive)
        "outline": "&H00000000",
        "back": "&H00000000",
        "bold": 1, "border_style": 1,
        "outline_w": 3, "shadow": 2,
        "alignment": 2, "margin_v": 280,
    },
    "neon": {
        "name": "Neon Glow",
        "font": "Rubik-Bold",
        "font_size": 74,
        "primary": "&H00FFFF00",      # Cyan
        "secondary": "&H00FFFFFF",
        "outline": "&H00FF00FF",       # Magenta
        "back": "&H00000000",
        "bold": 1, "border_style": 1,
        "outline_w": 4, "shadow": 3,
        "alignment": 2, "margin_v": 280,
    },
    "simple": {
        "name": "Simple Orange",
        "font": "Rubik-Bold",
        "font_size": 68,
        "primary": "&H00009CFF",       # Orange
        "secondary": "&H00FFFFFF",
        "outline": "&H00000000",
        "back": "&H80000000",
        "bold": 1, "border_style": 3,
        "outline_w": 2, "shadow": 2,
        "alignment": 2, "margin_v": 260,
    },
    "mrbeast": {
        "name": "MrBeast",
        "font": "Rubik-Bold",
        "font_size": 84,
        "primary": "&H0000FFFF",       # Yellow
        "secondary": "&H00FFFFFF",
        "outline": "&H00000000",
        "back": "&H00000000",
        "bold": 1, "border_style": 1,
        "outline_w": 5, "shadow": 3,
        "alignment": 2, "margin_v": 300,
    },
}


def _fmt_time(seconds):
    """Convert seconds to ASS format H:MM:SS.cc"""
    if seconds < 0:
        seconds = 0
    h = int(seconds // 3600)
    m = int((seconds % 3600) // 60)
    s = seconds % 60
    return f"{h}:{m:02d}:{s:05.2f}"


def _group_words_into_lines(words, max_chars=32, max_dur=3.5):
    """Group words into lines untuk karaoke."""
    lines = []
    current_line = []
    current_chars = 0
    line_start = None

    for w in words:
        w_text = (w.get("word") or w.get("text") or "").strip()
        if not w_text:
            continue
        w_start = float(w.get("start", 0))
        w_end = float(w.get("end", w_start + 0.3))
        
        if line_start is None:
            line_start = w_start
        
        added_chars = len(w_text) + (1 if current_line else 0)
        line_dur = w_end - line_start
        
        if current_line and (current_chars + added_chars > max_chars or line_dur > max_dur):
            lines.append(current_line)
            current_line = []
            current_chars = 0
            line_start = w_start
        
        current_line.append({"word": w_text, "start": w_start, "end": w_end})
        current_chars += added_chars
    
    if current_line:
        lines.append(current_line)
    
    return lines


def _build_ass_header(style_config, play_res):
    """Build ASS header."""
    w, h = play_res
    s = style_config
    style_line = (
        "Style: Default,{},{},{},{},{},{},"
        "{},0,0,0,100,100,0,0,"
        "{},{},{},{},20,20,{},1"
    ).format(
        s['font'], s['font_size'],
        s['primary'], s['secondary'], s['outline'], s['back'],
        s['bold'],
        s['border_style'], s['outline_w'], s['shadow'],
        s['alignment'], s['margin_v']
    )
    
    header = (
        "[Script Info]\n"
        "ScriptType: v4.00+\n"
        "PlayResX: {}\n"
        "PlayResY: {}\n"
        "ScaledBorderAndShadow: yes\n"
        "YCbCr Matrix: TV.601\n"
        "WrapStyle: 0\n"
        "\n"
        "[V4+ Styles]\n"
        "Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding\n"
        "{}\n"
        "\n"
        "[Events]\n"
        "Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text\n"
    ).format(w, h, style_line)
    return header


def _build_karaoke_text(line_words):
    r"""V9.96.0: Pakai \kf (fill) — sweep per kata, lebih halus."""
    parts = []
    for i, w in enumerate(line_words):
        dur_cs = max(1, int(round((w["end"] - w["start"]) * 100)))
        prefix = " " if i > 0 else ""
        # \kf = fill karaoke (sweep dari kiri ke kanan)
        # \K = sama seperti \kf (capital K)
        parts.append("{}{{\\kf{}}}{}".format(prefix, dur_cs, w['word']))
    return "".join(parts)


def save_karaoke_ass(words, output_ass_path, style="tiktok", play_res=(1080, 1920), max_chars=32, max_dur=3.5):
    """Generate ASS karaoke subtitle."""
    style_config = KARAOKE_STYLES.get(style, KARAOKE_STYLES["tiktok"])
    header = _build_ass_header(style_config, play_res)
    lines = _group_words_into_lines(words, max_chars=max_chars, max_dur=max_dur)
    
    dialogues = []
    for line_words in lines:
        if not line_words:
            continue
        start = line_words[0]["start"]
        end = line_words[-1]["end"]
        text = _build_karaoke_text(line_words)
        dialogue = "Dialogue: 0,{},{},Default,,0,0,0,,{}".format(
            _fmt_time(start), _fmt_time(end), text
        )
        dialogues.append(dialogue)
    
    with open(output_ass_path, "w", encoding="utf-8") as f:
        f.write(header)
        f.write("\n".join(dialogues))
        f.write("\n")
    
    return output_ass_path


def get_available_karaoke_styles():
    """Return list of available karaoke styles."""
    return list(KARAOKE_STYLES.keys())
