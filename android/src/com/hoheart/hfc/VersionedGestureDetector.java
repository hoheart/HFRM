package com.hoheart.hfc;

public abstract class VersionedGestureDetector {
//    private static final String TAG = "VersionedGestureDetector";
//    OnGestureListener mListener;
//    public static VersionedGestureDetector newInstance(Context context,
//            OnGestureListener listener) {  //设计实例化构造方法，这里Android123提示大家目前有3种API的实现方法，我们需要逐一考虑最优的解决方法，以满足高平台更多的功能实现。
//        final int sdkVersion = Integer.parseInt(Build.VERSION.SDK); //使用android.os.Build判断API Level，但需要将字符串转换为整形
//        VersionedGestureDetector detector = null;
//        if (sdkVersion < Build.VERSION_CODES.ECLAIR) { //如果版本小于2.0则使用1.5版本的API，可以兼容1.5和1.6
//            detector = new CupcakeDetector();
//        } else if (sdkVersion < Build.VERSION_CODES.FROYO) { //如果版本小于2.1则使用2.0版本的API，可以兼容2.0,2.0.1和2.1这三个版本
//            detector = new EclairDetector();
//        } else {  //否则使用2.2开始的新的触控API
//            detector = new FroyoDetector(context);
//        }
//        Log.d(TAG, "Created new " + detector.getClass()); //判断最终选择的到底是哪个版本的类
//        detector.mListener = listener;
//        return detector;
//    }
//    public abstract boolean onTouchEvent(MotionEvent ev); //我们需要根据版本决定onTouchEvent的实现
//    public interface OnGestureListener { //手势判断接口主要是实现两个方法
//        public void onDrag(float dx, float dy);  //拖拽
//        public void onScale(float scaleFactor); //缩放
//    }
//    private static class CupcakeDetector extends VersionedGestureDetector { //针对Android 1.5和1.6设计的兼容方式
//        float mLastTouchX;
//        float mLastTouchY;
//        float getActiveX(MotionEvent ev) { //获得当前X坐标
//            return ev.getX();
//        }
//        float getActiveY(MotionEvent ev) { //获得当前Y坐标
//            return ev.getY();
//        }
//        boolean shouldDrag() { //是否是拖拽中或者说移动中
//            return true;
//        }
//        @Override
//        public boolean onTouchEvent(MotionEvent ev) { //重写onTouchEvent方法
//            switch (ev.getAction()) {
//            case MotionEvent.ACTION_DOWN: {  //向下
//                mLastTouchX = getActiveX(ev);
//                mLastTouchY = getActiveY(ev);
//                break;
//            }
//            case MotionEvent.ACTION_MOVE: { //Android开发网提醒大家，由于1.x时代的API比较简单，很多手势没有封装，我们只能从ACTION_MOVE中根据坐标变化判断手势样式
//                final float x = getActiveX(ev);
//                final float y = getActiveY(ev);
//                if (shouldDrag()) {
//                    mListener.onDrag(x - mLastTouchX, y - mLastTouchY); //处理拖拽移动
//                }
//                mLastTouchX = x;
//                mLastTouchY = y;
//                break;
//            }
//            }
//            return true;
//        }
//    }
//    private static class EclairDetector extends CupcakeDetector { //这个是针对Android 2.0,2.0.1和2.1提供的解决方法，可以看到有很多多点触控相关API出现
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
//            case MotionEvent.ACTION_POINTER_UP: //有个点松开
//                final int pointerIndex = (ev.getAction() & MotionEvent.ACTION_POINTER_INDEX_MASK)
//                        >> MotionEvent.ACTION_POINTER_INDEX_SHIFT;
//                final int pointerId = ev.getPointerId(pointerIndex); //获取第几个点
//                if (pointerId == mActivePointerId) {
//                    final int newPointerIndex = pointerIndex == 0 ? 1 : 0;
//                    mActivePointerId = ev.getPointerId(newPointerIndex);
//                    mLastTouchX = ev.getX(newPointerIndex); //处理第newPointerIndex个点的x位置
//                    mLastTouchY = ev.getY(newPointerIndex);
//                }
//                break;
//            }
//            mActivePointerIndex = ev.findPointerIndex(mActivePointerId);
//            return super.onTouchEvent(ev);
//        }
//    }
//    private static class FroyoDetector extends EclairDetector { //从Android 2.2开始可以很好的处理多点触控的缩放问题
//        private ScaleGestureDetector mDetector;
//        public FroyoDetector(Context context) {
//            mDetector = new ScaleGestureDetector(context,
//                    new ScaleGestureDetector.SimpleOnScaleGestureListener() {
//                @Override public boolean onScale(ScaleGestureDetector detector) {
//                    mListener.onScale(detector.getScaleFactor()); //根据 ScaleGestureDetector.SimpleOnScaleGestureListener这个系统类处理缩放情况通过onScale方法
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
// 有关调用方法，我们可以自定义一个View，取名为TouchExampleView类，这里来处理触控相关的问题
//public class TouchExampleView extends View {
//    private Drawable mIcon; //我们以一个图片为参照物，根据手势控制
//    private float mPosX;
//    private float mPosY;
//    private VersionedGestureDetector mDetector;
//    private float mScaleFactor = 1.f; //原始缩放比例为1.0
//    public TouchExampleView(Context context) {
//        this(context, null, 0);
//    }
//    public TouchExampleView(Context context, AttributeSet attrs) {
//        this(context, attrs, 0);
//    }
//    public TouchExampleView(Context context, AttributeSet attrs, int defStyle) { //实现我们自定义View的构造
//        super(context, attrs, defStyle);
//        mIcon = context.getResources().getDrawable(R.drawable.icon);
//        mIcon.setBounds(0, 0, mIcon.getIntrinsicWidth(), mIcon.getIntrinsicHeight());
//        mDetector = VersionedGestureDetector.newInstance(context, new GestureCallback()); //实例化刚才的版本自适应手势控制类
//    }
//    @Override
//    public boolean onTouchEvent(MotionEvent ev) { //重写onTouchEvent方法，使用VersionedGestureDetector类得出的数据。
//        mDetector.onTouchEvent(ev);
//        return true;
//    }
//    @Override
//    public void onDraw(Canvas canvas) { //处理自定义View绘制方法
//        super.onDraw(canvas);
//        canvas.save();
//        canvas.translate(mPosX, mPosY); //进行平移操作，根据mPosX和mPosY坐标
//        canvas.scale(mScaleFactor, mScaleFactor); //进行缩放操作，参数就是刚才定义的float类型的缩放比例
//        mIcon.draw(canvas); //直接绘制图片变化到画布中
//        canvas.restore();
//    }
//    private class GestureCallback implements VersionedGestureDetector.OnGestureListener {
//        public void onDrag(float dx, float dy) { //这里Android123提示大家在2.2中这个回调方法将可以支持拖拽的坐标处理
//            mPosX += dx;
//            mPosY += dy;
//            invalidate();
//        }
//        public void onScale(float scaleFactor) {
//            mScaleFactor *= scaleFactor; //缩放控制
//            mScaleFactor = Math.max(0.1f, Math.min(mScaleFactor, 5.0f)); //限制最小缩放比例为1.0最大为5.0倍数
//            invalidate();
//        }
//    }
 }