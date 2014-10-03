package com.hoheart.hfc;

public abstract class VersionedGestureDetector {
//    private static final String TAG = "VersionedGestureDetector";
//    OnGestureListener mListener;
//    public static VersionedGestureDetector newInstance(Context context,
//            OnGestureListener listener) {  //���ʵ�������췽��������Android123��ʾ���Ŀǰ��3��API��ʵ�ַ�����������Ҫ��һ�������ŵĽ���������������ƽ̨����Ĺ���ʵ�֡�
//        final int sdkVersion = Integer.parseInt(Build.VERSION.SDK); //ʹ��android.os.Build�ж�API Level������Ҫ���ַ���ת��Ϊ����
//        VersionedGestureDetector detector = null;
//        if (sdkVersion < Build.VERSION_CODES.ECLAIR) { //����汾С��2.0��ʹ��1.5�汾��API�����Լ���1.5��1.6
//            detector = new CupcakeDetector();
//        } else if (sdkVersion < Build.VERSION_CODES.FROYO) { //����汾С��2.1��ʹ��2.0�汾��API�����Լ���2.0,2.0.1��2.1�������汾
//            detector = new EclairDetector();
//        } else {  //����ʹ��2.2��ʼ���µĴ���API
//            detector = new FroyoDetector(context);
//        }
//        Log.d(TAG, "Created new " + detector.getClass()); //�ж�����ѡ��ĵ������ĸ��汾����
//        detector.mListener = listener;
//        return detector;
//    }
//    public abstract boolean onTouchEvent(MotionEvent ev); //������Ҫ���ݰ汾����onTouchEvent��ʵ��
//    public interface OnGestureListener { //�����жϽӿ���Ҫ��ʵ����������
//        public void onDrag(float dx, float dy);  //��ק
//        public void onScale(float scaleFactor); //����
//    }
//    private static class CupcakeDetector extends VersionedGestureDetector { //���Android 1.5��1.6��Ƶļ��ݷ�ʽ
//        float mLastTouchX;
//        float mLastTouchY;
//        float getActiveX(MotionEvent ev) { //��õ�ǰX����
//            return ev.getX();
//        }
//        float getActiveY(MotionEvent ev) { //��õ�ǰY����
//            return ev.getY();
//        }
//        boolean shouldDrag() { //�Ƿ�����ק�л���˵�ƶ���
//            return true;
//        }
//        @Override
//        public boolean onTouchEvent(MotionEvent ev) { //��дonTouchEvent����
//            switch (ev.getAction()) {
//            case MotionEvent.ACTION_DOWN: {  //����
//                mLastTouchX = getActiveX(ev);
//                mLastTouchY = getActiveY(ev);
//                break;
//            }
//            case MotionEvent.ACTION_MOVE: { //Android���������Ѵ�ң�����1.xʱ����API�Ƚϼ򵥣��ܶ�����û�з�װ������ֻ�ܴ�ACTION_MOVE�и�������仯�ж�������ʽ
//                final float x = getActiveX(ev);
//                final float y = getActiveY(ev);
//                if (shouldDrag()) {
//                    mListener.onDrag(x - mLastTouchX, y - mLastTouchY); //������ק�ƶ�
//                }
//                mLastTouchX = x;
//                mLastTouchY = y;
//                break;
//            }
//            }
//            return true;
//        }
//    }
//    private static class EclairDetector extends CupcakeDetector { //��������Android 2.0,2.0.1��2.1�ṩ�Ľ�����������Կ����кܶ��㴥�����API����
//        private static final int INVALID_POINTER_ID = -1;
//        private int mActivePointerId = INVALID_POINTER_ID;
//        private int mActivePointerIndex = 0;
//        @Override
//        float getActiveX(MotionEvent ev) {
//            return ev.getX(mActivePointerIndex);
//        }
//        @Override
//        float getActiveY(MotionEvent ev) {
//            return ev.getY(mActivePointerIndex);
//        }
//        @Override
//        public boolean onTouchEvent(MotionEvent ev) {
//            final int action = ev.getAction();
//            switch (action & MotionEvent.ACTION_MASK) {
//            case MotionEvent.ACTION_DOWN:
//                mActivePointerId = ev.getPointerId(0);
//                break;
//            case MotionEvent.ACTION_CANCEL:
//            case MotionEvent.ACTION_UP:
//                mActivePointerId = INVALID_POINTER_ID;
//                break;
//            case MotionEvent.ACTION_POINTER_UP: //�и����ɿ�
//                final int pointerIndex = (ev.getAction() & MotionEvent.ACTION_POINTER_INDEX_MASK)
//                        >> MotionEvent.ACTION_POINTER_INDEX_SHIFT;
//                final int pointerId = ev.getPointerId(pointerIndex); //��ȡ�ڼ�����
//                if (pointerId == mActivePointerId) {
//                    final int newPointerIndex = pointerIndex == 0 ? 1 : 0;
//                    mActivePointerId = ev.getPointerId(newPointerIndex);
//                    mLastTouchX = ev.getX(newPointerIndex); //�����newPointerIndex�����xλ��
//                    mLastTouchY = ev.getY(newPointerIndex);
//                }
//                break;
//            }
//            mActivePointerIndex = ev.findPointerIndex(mActivePointerId);
//            return super.onTouchEvent(ev);
//        }
//    }
//    private static class FroyoDetector extends EclairDetector { //��Android 2.2��ʼ���ԺܺõĴ����㴥�ص���������
//        private ScaleGestureDetector mDetector;
//        public FroyoDetector(Context context) {
//            mDetector = new ScaleGestureDetector(context,
//                    new ScaleGestureDetector.SimpleOnScaleGestureListener() {
//                @Override public boolean onScale(ScaleGestureDetector detector) {
//                    mListener.onScale(detector.getScaleFactor()); //���� ScaleGestureDetector.SimpleOnScaleGestureListener���ϵͳ�ദ���������ͨ��onScale����
//                    return true;
//                }
//            });
//        }
//        @Override
//        boolean shouldDrag() {
//            return !mDetector.isInProgress();
//        }
//        @Override
//        public boolean onTouchEvent(MotionEvent ev) {
//            mDetector.onTouchEvent(ev);
//            return super.onTouchEvent(ev);
//        }
//    }
//}
// �йص��÷��������ǿ����Զ���һ��View��ȡ��ΪTouchExampleView�࣬��������������ص�����
//public class TouchExampleView extends View {
//    private Drawable mIcon; //������һ��ͼƬΪ������������ƿ���
//    private float mPosX;
//    private float mPosY;
//    private VersionedGestureDetector mDetector;
//    private float mScaleFactor = 1.f; //ԭʼ���ű���Ϊ1.0
//    public TouchExampleView(Context context) {
//        this(context, null, 0);
//    }
//    public TouchExampleView(Context context, AttributeSet attrs) {
//        this(context, attrs, 0);
//    }
//    public TouchExampleView(Context context, AttributeSet attrs, int defStyle) { //ʵ�������Զ���View�Ĺ���
//        super(context, attrs, defStyle);
//        mIcon = context.getResources().getDrawable(R.drawable.icon);
//        mIcon.setBounds(0, 0, mIcon.getIntrinsicWidth(), mIcon.getIntrinsicHeight());
//        mDetector = VersionedGestureDetector.newInstance(context, new GestureCallback()); //ʵ�����ղŵİ汾����Ӧ���ƿ�����
//    }
//    @Override
//    public boolean onTouchEvent(MotionEvent ev) { //��дonTouchEvent������ʹ��VersionedGestureDetector��ó������ݡ�
//        mDetector.onTouchEvent(ev);
//        return true;
//    }
//    @Override
//    public void onDraw(Canvas canvas) { //�����Զ���View���Ʒ���
//        super.onDraw(canvas);
//        canvas.save();
//        canvas.translate(mPosX, mPosY); //����ƽ�Ʋ���������mPosX��mPosY����
//        canvas.scale(mScaleFactor, mScaleFactor); //�������Ų������������ǸղŶ����float���͵����ű���
//        mIcon.draw(canvas); //ֱ�ӻ���ͼƬ�仯��������
//        canvas.restore();
//    }
//    private class GestureCallback implements VersionedGestureDetector.OnGestureListener {
//        public void onDrag(float dx, float dy) { //����Android123��ʾ�����2.2������ص�����������֧����ק�����괦��
//            mPosX += dx;
//            mPosY += dy;
//            invalidate();
//        }
//        public void onScale(float scaleFactor) {
//            mScaleFactor *= scaleFactor; //���ſ���
//            mScaleFactor = Math.max(0.1f, Math.min(mScaleFactor, 5.0f)); //������С���ű���Ϊ1.0���Ϊ5.0����
//            invalidate();
//        }
//    }
 }