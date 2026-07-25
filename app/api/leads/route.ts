import { NextRequest, NextResponse } from 'next/server';
import dbConnect from '@/lib/mongodb';
import Lead from '@/models/Lead';

export async function POST(req: NextRequest) {
  try {
    await dbConnect();
    const { email, lang } = await req.json();

    if (!email || typeof email !== 'string' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      return NextResponse.json({ error: 'Valid email required' }, { status: 400 });
    }

    await Lead.create({ email: email.toLowerCase().trim(), lang: lang === 'sl' ? 'sl' : 'en' });
    return NextResponse.json({ success: true }, { status: 201 });
  } catch (error: any) {
    if (error.code === 11000) {
      return NextResponse.json({ success: true, message: 'Already exists' }, { status: 200 });
    }
    console.error('Lead creation error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}
